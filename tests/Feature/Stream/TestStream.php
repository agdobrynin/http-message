<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Feature\Stream;

class TestStream
{
    public $context;

    public function stream_eof()
    {
        return true;
    }

    public function stream_open()
    {
        return true;
    }

    public function stream_read()
    {
        return false;
    }

    public function stream_seek(int $offset, int $whence)
    {
        return false;
    }

    public function stream_set_option(): bool
    {
        return false;
    }

    public function stream_stat()
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

    public function stream_tell()
    {
        return false;
    }

    public function stream_truncate()
    {
        return false;
    }

    public function stream_write(string $data)
    {
        return false;
    }
}
