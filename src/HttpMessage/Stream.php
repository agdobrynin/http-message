<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource
     */
    protected $resource;
    protected bool $writable;
    protected bool $readable;
    protected bool $seekable;
    protected ?int $size = null;
    protected ?string $uri = null;

    public function __construct(mixed $body)
    {
        if (!\is_string($body) && !\is_resource($body)) {
            throw new \InvalidArgumentException('Argument must be type "resource" or "string"');
        }

        if (\is_string($body)) {
            $this->uri = 'php://temp';
            $this->resource = \fopen($this->uri, 'r+b') ?: throw new \RuntimeException('Cannot open stream [php://temp]');
            \fwrite($this->resource, $body);
            \fseek($this->resource, 0);
            $this->size = \strlen($body);
            $this->writable = $this->readable = $this->seekable = true;
        } else {
            $this->resource = $body;
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
            if (\is_resource($this->resource)) {
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

        if ($this->size) {
            return $this->size;
        }

        if ($this->uri) {
            \clearstatcache(true, $this->uri);
        }

        return \fstat($this->resource)['size'] ?? null;
    }

    public function tell(): int
    {
        if (isset($this->resource) && \is_resource($this->resource)) {
            if (false !== ($pos = @\ftell($this->resource))) {
                return $pos;
            }

            // @codeCoverageIgnoreStart
            $this->exceptionWithLastError('Cant get pointer position of stream');
            // @codeCoverageIgnoreEnd
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
        if (!isset($this->resource)) {
            throw new \RuntimeException('Stream not defined');
        }

        if (!$this->writable) {
            throw new \RuntimeException('Stream is not writable');
        }

        $this->size = null; // unset size of stream.
        $bytes = @\fwrite($this->resource, $string);

        if (false === $bytes) {
            $this->exceptionWithLastError('Cannot write to stream');
        }

        return $bytes;
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

        $content = @\fread($this->resource, $length);

        if (false === $content) {
            $this->exceptionWithLastError('Cannot read from stream');
        }

        return $content;
    }

    public function getContents(): string
    {
        if (!isset($this->resource)) {
            throw new \RuntimeException('Stream not defined');
        }

        $contents = @\stream_get_contents($this->resource);

        if (false === $contents) {
            // @codeCoverageIgnoreStart
            $this->exceptionWithLastError('Cannot read stream contents');
            // @codeCoverageIgnoreEnd
        }

        return $contents;
    }

    public function getMetadata(?string $key = null): mixed
    {
        if (isset($this->resource) && \is_resource($this->resource)) {
            $meta = \stream_get_meta_data($this->resource);

            return null === $key ? $meta : ($meta[$key] ?? null);
        }

        return null === $key ? [] : null;
    }

    protected function exceptionWithLastError(string $mainMessage): never
    {
        throw new \RuntimeException($mainMessage.': '.(\error_get_last()['message'] ?? ''));
    }
}
