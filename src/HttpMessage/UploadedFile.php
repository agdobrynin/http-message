<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private const UPLOAD_ERR = [
        \UPLOAD_ERR_OK,
        \UPLOAD_ERR_INI_SIZE,
        \UPLOAD_ERR_FORM_SIZE,
        \UPLOAD_ERR_PARTIAL,
        \UPLOAD_ERR_NO_FILE,
        \UPLOAD_ERR_NO_TMP_DIR,
        \UPLOAD_ERR_CANT_WRITE,
        \UPLOAD_ERR_EXTENSION,
    ];

    /**
     * File with full path.
     */
    private ?string $file;

    /**
     * Stream of file.
     */
    private ?StreamInterface $stream;

    /**
     * True then the file was successfully moved using UploadedFile::moveTo.
     */
    private bool $moved = false;

    /**
     * @param StreamInterface|string $fileOrStream    file with path or StreamInterface of uploaded file
     * @param ?int                   $size            uploaded file size
     * @param null|string            $originFileName  the value stored in the "name" key of the file in the $_FILES array
     * @param null|string            $originMediaType the value stored in the "type" key of the file in the $_FILES array
     */
    public function __construct(
        StreamInterface|string $fileOrStream,
        private readonly int $error,
        private readonly ?int $size = null,
        private readonly ?string $originFileName = null,
        private readonly ?string $originMediaType = null
    ) {
        if (!isset(self::UPLOAD_ERR[$this->error])) {
            throw new \InvalidArgumentException('Invalid upload file error. Got: '.$this->error);
        }

        if (\UPLOAD_ERR_OK === $this->error) {
            if ($fileOrStream instanceof StreamInterface) {
                $this->stream = $fileOrStream;
            } elseif ('' !== $fileOrStream) {
                $this->file = $fileOrStream;
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

        $resource = @\fopen($this->file, 'rb');

        if (false === $resource) {
            $error = \error_get_last()['message'] ?? '';

            throw new \RuntimeException("Cannot open file {$this->file}  [{$error}]");
        }

        return new Stream($resource);
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
            $resourceDest = @\fopen($targetPath, 'wb');

            if (false === $resourceDest) {
                $message = \error_get_last()['message'] ?? '';

                throw new \RuntimeException("Cannot open target file {$targetPath} [{$message}]");
            }

            $dest = new Stream($resourceDest);

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

    private function isAvailable(): void
    {
        if (\UPLOAD_ERR_OK !== $this->error) {
            throw new \RuntimeException('Upload file has error code: '.$this->error);
        }

        if ($this->moved) {
            throw new \RuntimeException('The uploaded file has already been moved');
        }
    }
}
