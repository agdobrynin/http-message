<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Message;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetHeaders;
use Tests\Kaspi\HttpMessage\DatasetMessageBody;
use Tests\Kaspi\HttpMessage\StreamAdapter;
use TypeError;

use function fopen;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
#[CoversClass(PhpTempStream::class)]
class MessageConstructorTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $m = new Message(StreamAdapter::make());

        self::assertEquals(0, $m->getBody()->getSize());
        self::assertEquals('', (string) $m->getBody());
        self::assertEquals('1.1', $m->getProtocolVersion());
        self::assertEquals([], $m->getHeaders());
    }

    #[DataProvider('dataProviderTestBody')]
    public function testBody($body, $contents): void
    {
        self::assertEquals($contents, (new Message(body: $body))->getBody());
    }

    public static function dataProviderTestBody(): Generator
    {
        yield from DatasetMessageBody::messageBodySuccess();

        yield 'from resource' => [
            new Stream(fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb')),
            'Virtual file!',
        ];
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'messageBodyWrong')]
    public function testBodyWrongType($body): void
    {
        $this->expectException(TypeError::class);

        new Message(body: $body);
    }

    #[DataProviderExternal(DatasetMessageBody::class, 'protocolSuccess')]
    public function testProtocolVersion($version): void
    {
        $m = new Message(StreamAdapter::make(), protocolVersion: $version);

        self::assertEquals($version, $m->getProtocolVersion());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersSuccess')]
    public function testHeadersSuccess($headers, $expectHeaders): void
    {
        self::assertEquals($expectHeaders, (new Message(headers: $headers))->getHeaders());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWrong')]
    public function testHeadersWrong($headers, $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Message(StreamAdapter::make(), headers: $headers);
    }
}
