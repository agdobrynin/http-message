<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Request;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetHeaders;
use Tests\Kaspi\HttpMessage\DatasetMessageBody;
use Tests\Kaspi\HttpMessage\DatasetUri;
use TypeError;

use function fopen;

/**
 * @internal
 */
#[CoversClass(Request::class)]
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
#[CoversClass(Uri::class)]
class RequestConstructorTest extends TestCase
{
    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodySuccess')]
    #[DataProvider('dataProviderSuccessParameterBody')]
    public function testSuccessParameterBody($body, $contents): void
    {
        self::assertEquals($contents, (string) (new Request(body: $body))->getBody());
    }

    public static function dataProviderSuccessParameterBody(): Generator
    {
        yield 'from resource' => [
            new Stream(fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb')),
            'Virtual file!',
        ];
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodyWrong')]
    public function testWrongParameterBody($body): void
    {
        $this->expectException(TypeError::class);

        new Request(body: $body);
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'protocolSuccess')]
    public function testProtocolVersion($version): void
    {
        self::assertEquals($version, (new Request(protocolVersion: $version))->getProtocolVersion());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersSuccess')]
    public function testSuccessHeaders($headers, $expectHeaders): void
    {
        self::assertEquals($expectHeaders, (new Request(headers: $headers))->getHeaders());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWrong')]
    public function testWrongHeaders($headers, $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Request(headers: $headers);
    }

    #[DataProviderExternal(DatasetUri::class, 'uriSuccess')]
    public function testSuccessParametersUri($uri, $uriExpect): void
    {
        self::assertEquals($uriExpect, (string) (new Request(uri: $uri))->getUri());
    }
}
