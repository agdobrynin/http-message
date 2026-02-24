<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\HttpFactory;

use Generator;
use Kaspi\HttpMessage\HttpFactory;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\UploadedFile;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\Dataset;
use Tests\Kaspi\HttpMessage\StreamAdapter;

use function fopen;

/**
 * @internal
 */
#[CoversClass(HttpFactory::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
#[CoversClass(Response::class)]
#[CoversClass(Uri::class)]
#[CoversClass(ServerRequest::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
#[CoversClass(FileStream::class)]
#[CoversClass(UploadedFile::class)]
class HttpFactoryTest extends TestCase
{
    #[DataProviderExternal(Dataset::class, 'httpFactoryRequest')]
    public function testMethodAndURI($method, $uri, $expectUri): void
    {
        $r = (new HttpFactory())->createRequest($method, $uri);

        self::assertEquals($method, $r->getMethod());
        self::assertEquals($expectUri, $r->getUri());
    }

    #[DataProvider('dataProviderHttpStatusCodeAndResponsePhrase')]
    public function testHttpStatusCodeAndResponsePhrase(array $args, $expectCode, $expectPhrase): void
    {
        $r = (new HttpFactory())->createResponse(...$args);

        self::assertEquals($expectCode, $r->getStatusCode());
        self::assertEquals($expectPhrase, $r->getReasonPhrase());
    }

    public static function dataProviderHttpStatusCodeAndResponsePhrase(): Generator
    {
        yield 'all default' => [
            [],
            200,
            'OK',
        ];

        yield 'standard http status 404' => [
            [404],
            404,
            'Not Found',
        ];

        yield 'standard http status 599' => [
            [599],
            599,
            '',
        ];

        yield 'standard http status 511' => [
            [511],
            511,
            'Network Authentication Required',
        ];

        yield 'standard http status and custom response phrase' => [
            [201, 'Account created success. You can login now.'],
            201,
            'Account created success. You can login now.',
        ];
    }

    #[DataProviderExternal(Dataset::class, 'httpFactoryServerRequest')]
    public function testCreateServerRequest($method, $uri, $srvParams, $expectUri): void
    {
        $s = (new HttpFactory())->createServerRequest($method, $uri, $srvParams);

        self::assertEquals($expectUri, (string) $s->getUri());
        self::assertEquals($method, $s->getMethod());
        self::assertEquals($srvParams, $s->getServerParams());
    }

    public function testCreateStream(): void
    {
        $s = (new HttpFactory())->createStream('hello world');

        self::assertEquals('hello world', $s->getContents());
    }

    public function testCreateStreamFromResource(): void
    {
        $f = vfsStream::newFile('i')->withContent('hello world')->at(vfsStream::setup());

        $s = (new HttpFactory())->createStreamFromResource(fopen($f->url(), 'rb'));

        self::assertEquals('hello world', $s->getContents());
    }

    #[DataProviderExternal(Dataset::class, 'uriAsString')]
    public function testCreateUri($uri, $expectUri): void
    {
        $u = (new HttpFactory())->createUri($uri);

        self::assertEquals($expectUri, (string) $u);
    }

    public function testCreateStreamFromFileSuccess(): void
    {
        $f = vfsStream::newFile('i')->withContent('hello world')->at(vfsStream::setup());

        $s = (new HttpFactory())->createStreamFromFile($f->url());

        self::assertEquals('hello world', (string) $s);
    }

    #[DataProvider('dataProviderUploadedFile')]
    public function testCreateUploadedFileWithoutSizeFile($stream, $size, $expectSize): void
    {
        $uf = (new HttpFactory())->createUploadedFile(stream: $stream, size: $size);

        self::assertEquals($expectSize, $uf->getSize());
    }

    public static function dataProviderUploadedFile(): Generator
    {
        yield 'null init size' => [
            StreamAdapter::make('file'),
            null,
            4,
        ];

        yield 'init size' => [
            StreamAdapter::make('file'),
            10,
            10,
        ];
    }
}
