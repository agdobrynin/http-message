<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\HttpFactory;

use Generator;
use Kaspi\HttpMessage\HttpFactory;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function restore_error_handler;
use function set_error_handler;
use function uniqid;

/**
 * @internal
 */
#[CoversClass(HttpFactory::class)]
#[CoversClass(FileStream::class)]
#[CoversClass(Stream::class)]
class HttpFactoryFailTest extends TestCase
{
    protected function tearDown(): void
    {
        restore_error_handler();
    }

    #[DataProvider('dataProviderFailForRead')]
    public function testFailForRead(string $file, string $mode, string $message): void
    {
        set_error_handler(static fn () => false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        (new HttpFactory())->createStreamFromFile($file, $mode);
    }

    public static function dataProviderFailForRead(): Generator
    {
        yield 'empty name' => [
            '',
            'r',
            'Path cannot',
        ];

        yield 'file not found' => [
            __DIR__.DIRECTORY_SEPARATOR.uniqid('test'),
            'rb',
            'No such file or directory',
        ];

        vfsStream::setup(structure: [
            'my.txt' => 'content',
        ]);

        yield 'fail mode' => [
            vfsStream::url('root/my.txt'),
            'foo',
            'Failed to open stream',
        ];

        $root = vfsStream::setup();
        vfsStream::newFile('only_write.txt', 0222)->at($root);

        yield 'mode cannot read stream' => [
            vfsStream::url('root/only_write.txt'),
            'r+b',
            'Failed to open stream',
        ];

        vfsStream::newFile('only_read.txt', 0444)->at($root);

        yield 'mode write' => [
            vfsStream::url('root/only_read.txt'),
            'w+b',
            'Failed to open stream',
        ];
    }
}
