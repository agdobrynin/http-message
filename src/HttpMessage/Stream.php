<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource
     */
    private $resource;
    private bool $writable;
    private bool $readable;
    private bool $seekable;
    private ?int $size = null;
    private ?string $uri = null;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!\is_resource($resource)) {
            $got = \var_export($resource, true);

            throw new \InvalidArgumentException('Argument must be type "resource" or "string". Got: '.$got);
        }

        $this->resource = $resource;
        $meta = \stream_get_meta_data($this->resource);
        $this->uri = $meta['uri'] ?? null;
        $this->seekable = ($meta['seekable'] ?? null)
            && 0 === \fseek($this->resource, 0, \SEEK_CUR);
        $mode = ($meta['mode'] ?? '');

        if (\str_contains($mode, '+')) {
            $this->writable = $this->readable = true;
        } else {
            $this->writable = \str_contains($mode, 'w') || \str_contains($mode, 'a') || \str_contains($mode, 'c');
            $this->readable = \str_contains($mode, 'r');
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    public function close(): void
    {
        if (isset($this->resource)) {
            if ($this->isValidStream()) {
                \fclose($this->resource);
            }

            $this->detach();
        }
    }

    /**
     * @return null|resource
     */
    public function detach(): mixed
    {
        if (!isset($this->resource)) {
            return null;
        }

        $resource = $this->resource;
        // @phan-suppress-next-line PhanTypeObjectUnsetDeclaredProperty
        unset($this->resource);
        $this->writable = $this->readable = $this->seekable = false;
        $this->uri = $this->size = null;

        return $resource;
    }

    public function getSize(): ?int
    {
        if (!isset($this->resource)) {
            return null;
        }

        if (null !== $this->size) {
            return $this->size;
        }

        if ($this->uri) {
            \clearstatcache(true, $this->uri);
        }

        return $this->size = (\fstat($this->resource)['size'] ?? null);
    }

    public function tell(): int
    {
        if (isset($this->resource) && $this->isValidStream()) {
            return ($pos = @\ftell($this->resource)) !== false
                ? $pos
                : throw new \RuntimeException('Cant get pointer position of stream: '.(\error_get_last()['message'] ?? ''));
        }

        throw new \RuntimeException('Stream not defined');
    }

    public function eof(): bool
    {
        return !isset($this->resource) || \feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        if (!isset($this->resource)) {
            throw new \RuntimeException('Stream not defined');
        }

        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (-1 === \fseek($this->resource, $offset, $whence)) {
            $debugWhence = \var_export($whence, true);

            throw new \RuntimeException("Cannot search for position [{$offset}] in stream with whence [{$debugWhence}]");
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        if (!isset($this->resource) || !$this->isValidStream()) {
            throw new \RuntimeException('Stream not defined');
        }

        if (!$this->writable) {
            throw new \RuntimeException('Stream is not writable');
        }

        $this->size = null; // Nullable for size of stream (calc it later)

        return ($bytes = @\fwrite($this->resource, $string)) !== false
            ? $bytes
            : throw new \RuntimeException('Cannot write to stream: '.(\error_get_last()['message'] ?? ''));
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read(int $length): string
    {
        if (!isset($this->resource)) {
            throw new \RuntimeException('Stream not defined');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Stream is not readable');
        }

        return ($content = @\fread($this->resource, $length)) !== false
            ? $content
            : throw new \RuntimeException('Cannot read from stream: '.(\error_get_last()['message'] ?? ''));
    }

    public function getContents(): string
    {
        if (!isset($this->resource) || !$this->isValidStream()) {
            throw new \RuntimeException('Stream not defined');
        }

        return ($contents = @\stream_get_contents($this->resource)) !== false
            ? $contents
            : throw new \RuntimeException('Cannot read stream contents: '.(\error_get_last()['message'] ?? ''));
    }

    public function getMetadata(?string $key = null): mixed
    {
        if (isset($this->resource) && $this->isValidStream()) {
            $meta = \stream_get_meta_data($this->resource);

            return null === $key ? $meta : ($meta[$key] ?? null);
        }

        return null === $key ? [] : null;
    }

    private function isValidStream(): bool
    {
        return \is_resource($this->resource)
            && 'Unknown' !== \get_resource_type($this->resource);
    }
}
