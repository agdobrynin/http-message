<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Response;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetHeaders;
use Tests\Kaspi\HttpMessage\DatasetMessageBody;
use Tests\Kaspi\HttpMessage\DatasetReasonPhrase;
use TypeError;

use function fopen;

/**
 * @internal
 */
#[CoversClass(Response::class)]
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
class ResponseConstructorTest extends TestCase
{
    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodySuccess')]
    #[DataProvider('dataProviderSuccessParameterBody')]
    public function testSuccessParameterBody($body, $contents): void
    {
        self::assertEquals($contents, (string) (new Response(body: $body))->getBody());
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

        new Response(body: $body);
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'protocolSuccess')]
    public function testProtocolVersion($version): void
    {
        self::assertEquals($version, (new Response(protocolVersion: $version))->getProtocolVersion());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersSuccess')]
    public function testSuccessHeaders($headers, $expectHeaders): void
    {
        self::assertEquals($expectHeaders, (new Response(headers: $headers))->getHeaders());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWrong')]
    public function testWrongHeaders($headers, $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Response(headers: $headers);
    }

    #[DataProviderExternal(DatasetReasonPhrase::class, 'reasonPhraseSuccess')]
    public function testNormalizeReasonPhrase($reasonPhrase, $expect): void
    {
        self::assertEquals($expect, (new Response(reasonPhrase: $reasonPhrase))->getReasonPhrase());
    }

    #[DataProviderExternal(DatasetReasonPhrase::class, 'reasonPhraseFail')]
    public function testWrongReasonPhrase($reasonPhrase): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(reasonPhrase: $reasonPhrase);
    }
}
