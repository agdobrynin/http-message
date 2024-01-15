<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private const UPLOAD_ERR = [
        \UPLOAD_ERR_OK => 1,
        \UPLOAD_ERR_INI_SIZE => 1,
        \UPLOAD_ERR_FORM_SIZE => 1,
        \UPLOAD_ERR_PARTIAL => 1,
        \UPLOAD_ERR_NO_FILE => 1,
        \UPLOAD_ERR_NO_TMP_DIR => 1,
        \UPLOAD_ERR_CANT_WRITE => 1,
        \UPLOAD_ERR_EXTENSION => 1,
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
            throw new \InvalidArgumentException('Invalid upload file error. Got: '.$this->error);
        }

        if ($this->isOk()) {
            if ($streamOrFile instanceof StreamInterface) {
                $this->stream = $streamOrFile;
            } elseif ('' !== $streamOrFile) {
                $this->file = $streamOrFile;
            } else {
                throw new \InvalidArgumentException(
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

        return ($r = @\fopen($this->file, 'rb')) !== false
            ? new Stream($r)
            : throw new \RuntimeException("Cannot open file {$this->file} [".\error_get_last()['message'] ?? ']');
    }

    public function moveTo(string $targetPath): void
    {
        $this->isAvailable();

        if ('' === $targetPath) {
            throw new \InvalidArgumentException('Target path must be provide non-empty string');
        }

        if ($this->file) {
            $this->moved = 'cli' === \PHP_SAPI
                ? @\rename($this->file, $targetPath)
                : @\move_uploaded_file($this->file, $targetPath);

            if (!$this->moved) {
                $error = \error_get_last()['message'] ?? '';

                throw new \RuntimeException("Cannot move uploaded file to {$targetPath} [{$error}]");
            }
        } else {
            $dest = ($r = @\fopen($targetPath, 'wb')) !== false
                ? new Stream($r)
                : throw new \RuntimeException("Cannot open target file {$targetPath} [".\error_get_last()['message'] ?? ']');

            $from = $this->getStream();

            if ($from->isSeekable()) {
                $from->rewind();
            }

            while (!$from->eof()) {
                if (!$dest->write($from->read(1048576))) {
                    break;
                }
            }

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

    private function isOk(): bool
    {
        return \UPLOAD_ERR_OK === $this->error;
    }

    private function isAvailable(): void
    {
        if (!$this->isOk()) {
            throw new \RuntimeException('Uploaded file has error code: '.$this->error);
        }

        if ($this->moved) {
            throw new \RuntimeException('The uploaded file has already been moved');
        }
    }
}
