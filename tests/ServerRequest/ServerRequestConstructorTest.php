<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\ServerRequest;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetHeaders;
use Tests\Kaspi\HttpMessage\DatasetMessageBody;
use Tests\Kaspi\HttpMessage\DatasetUri;
use TypeError;

/**
 * @internal
 */
#[CoversClass(ServerRequest::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
#[CoversClass(Uri::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
class ServerRequestConstructorTest extends TestCase
{
    #[DataProviderExternal(DatasetUri::class, 'uriSuccess')]
    public function testURI($uri, $uriExpect): void
    {
        self::assertEquals($uriExpect, (string) (new ServerRequest(uri: $uri))->getUri());
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodySuccess')]
    public function testSuccessBody($body, $contents): void
    {
        self::assertEquals($contents, (string) (new ServerRequest(body: $body))->getBody());
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodyWrong')]
    public function testFailBody($body): void
    {
        $this->expectException(TypeError::class);

        new ServerRequest(body: $body);
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersSuccess')]
    public function testHeadersSuccess($headers, $expectHeaders): void
    {
        self::assertEquals($expectHeaders, (new ServerRequest(headers: $headers))->getHeaders());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWrong')]
    public function testHeadersWrong($headers, $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new ServerRequest(headers: $headers);
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWithUri')]
    public function testHeadersWithURIHostHeader($uri, $headers, $expectHeaders): void
    {
        self::assertEquals($expectHeaders, (new ServerRequest(uri: $uri, headers: $headers))->getHeaders());
    }

    public function testDefaultProtocol(): void
    {
        self::assertEquals('1.1', (new ServerRequest())->getProtocolVersion());
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'protocolSuccess')]
    public function testSuccessProtocol($protocolVersion): void
    {
        self::assertEquals($protocolVersion, (new ServerRequest(protocolVersion: $protocolVersion))->getProtocolVersion());
    }

    #[DataProvider('dataProviderServerParameters')]
    public function testServerParameters(array $params): void
    {
        self::assertEquals($params, (new ServerRequest(serverParams: $params))->getServerParams());
    }

    public static function dataProviderServerParameters(): Generator
    {
        yield 'empty array' => [[]];

        yield 'has items' => [['first' => 'aaa']];
    }
}
