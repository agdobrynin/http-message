<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Stream\Fixtures;

class TestStream
{
    public $context;

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_open(): bool
    {
        return true;
    }

    public function stream_read(): bool
    {
        return false;
    }

    public function stream_seek(int $offset, int $whence): bool
    {
        return false;
    }

    public function stream_set_option(): bool
    {
        return false;
    }

    public function stream_stat(): array
    {
        return [
            'mode' => 33206, // POSIX_S_IFREG | 0666
            'nlink' => 1,
            'rdev' => -1,
            'size' => -1,
            'blksize' => -1,
            'blocks' => -1,
        ];
    }

    public function stream_tell(): bool
    {
        return false;
    }

    public function stream_truncate(): bool
    {
        return false;
    }

    public function stream_write(string $data): bool
    {
        return false;
    }
}
