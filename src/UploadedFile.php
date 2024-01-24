<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use InvalidArgumentException;
use Kaspi\HttpMessage\Stream\FileStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

use function error_get_last;
use function fopen;
use function is_string;
use function move_uploaded_file;
use function rename;

use const PHP_SAPI;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

class UploadedFile implements UploadedFileInterface
{
    private const UPLOAD_ERR = [
        UPLOAD_ERR_OK => 1,
        UPLOAD_ERR_INI_SIZE => 1,
        UPLOAD_ERR_FORM_SIZE => 1,
        UPLOAD_ERR_PARTIAL => 1,
        UPLOAD_ERR_NO_FILE => 1,
        UPLOAD_ERR_NO_TMP_DIR => 1,
        UPLOAD_ERR_CANT_WRITE => 1,
        UPLOAD_ERR_EXTENSION => 1,
    ];

    /**
     * File with full path.
     */
    private ?string $file = null;

    /**
     * Stream of file.
     */
    private ?StreamInterface $stream = null;

    /**
     * True then the file was successfully moved using UploadedFile::moveTo.
     */
    private bool $moved = false;

    /**
     * @param StreamInterface|string $streamOrFile    file with path or StreamInterface of uploaded file
     * @param ?int                   $size            uploaded file size
     * @param null|string            $originFileName  the value stored in the "name" key of the file in the $_FILES array
     * @param null|string            $originMediaType the value stored in the "type" key of the file in the $_FILES array
     */
    public function __construct(
        StreamInterface|string $streamOrFile,
        private readonly int $error,
        private readonly ?int $size = null,
        private readonly ?string $originFileName = null,
        private readonly ?string $originMediaType = null
    ) {
        if (!isset(self::UPLOAD_ERR[$this->error])) {
            throw new InvalidArgumentException('Invalid upload file error. Got: '.$this->error);
        }

        if (UPLOAD_ERR_OK === $this->error) {
            if (is_string($streamOrFile) && '' !== $streamOrFile) {
                $this->file = $streamOrFile;
            } elseif ($streamOrFile instanceof StreamInterface) {
                $this->stream = $streamOrFile;
            } else {
                throw new InvalidArgumentException(
                    'Invalid parameter. "fileOrStream" must provide non-empty string or '.StreamInterface::class
                );
            }
        }
    }

    public function getStream(): StreamInterface
    {
        $this->isAvailable();

        if ($this->stream) {
            return $this->stream;
        }

        return new FileStream($this->file, 'rb');
    }

    public function moveTo(string $targetPath): void
    {
        $this->isAvailable();

        if ('' === $targetPath) {
            throw new InvalidArgumentException('Target path must be provide non-empty string');
        }

        if ($this->file) {
            $this->moved = 'cli' === PHP_SAPI
                ? @rename($this->file, $targetPath)
            // @codeCoverageIgnoreStart
                : @move_uploaded_file($this->file, $targetPath);
            // @codeCoverageIgnoreEnd

            if (!$this->moved) {
                throw new RuntimeException(
                    "Cannot move uploaded file {$this->file} to {$targetPath} [".(error_get_last()['message'] ?? '').']'
                );
            }
        } else {
            if (($resource = @fopen($targetPath, 'wb+')) === false) {
                throw new RuntimeException("Cannot open from {$targetPath} [".(error_get_last()['message'] ?? '').']');
            }

            if (!$this->stream instanceof Stream) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('Property stream must be '.Stream::class);
                // @codeCoverageIgnoreEnd
            }

            // @phan-suppress-next-line PhanUndeclaredMethod
            $this->stream->copyTo($resource);
            $this->moved = true;
        }
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->originFileName;
    }

    public function getClientMediaType(): ?string
    {
        return $this->originMediaType;
    }

    private function isAvailable(): void
    {
        if (UPLOAD_ERR_OK !== $this->error) {
            throw new RuntimeException('Uploaded file has error code: '.$this->error);
        }

        if ($this->moved) {
            throw new RuntimeException('The uploaded file has already been moved');
        }
    }
}
