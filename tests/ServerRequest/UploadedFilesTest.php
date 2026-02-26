<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\ServerRequest;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\UploadedFile;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function tmpfile;

use const UPLOAD_ERR_OK;

/**
 * @internal
 */
#[CoversClass(ServerRequest::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
#[CoversClass(Uri::class)]
class UploadedFilesTest extends TestCase
{
    #[DataProvider('dataProviderOne')]
    public function testGetUploadedFilesWithUploadedFiles($files): void
    {
        $sr = new ServerRequest();

        self::assertEquals([], $sr->getUploadedFiles());

        $sr2 = $sr->withUploadedFiles($files);

        self::assertNotSame($sr, $sr2);
        self::assertEquals($files, $sr2->getUploadedFiles());
    }

    public static function dataProviderOne(): Generator
    {
        yield 'one level' => [
            [
                new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
            ],
        ];

        yield 'multi levels' => [
            [
                'avatars' => [
                    new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                    new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                    'notes' => [
                        new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                        new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('dataProviderTwo')]
    public function testFailWithUploadedFiles($files): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Items must be instance');

        (new ServerRequest())->withUploadedFiles($files);
    }

    public static function dataProviderTwo(): Generator
    {
        yield 'one level' => [
            [
                new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                '/tmp/my_file.jpg',
            ],
        ];

        yield 'many levels' => [
            [
                'avatars' => [
                    new UploadedFile(new Stream(tmpfile()), UPLOAD_ERR_OK),
                    'note' => [
                        '/tmp/my_file.jpg',
                    ],
                ],
            ],
        ];
    }
}
