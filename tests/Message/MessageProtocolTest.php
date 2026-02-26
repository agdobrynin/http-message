<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Message;

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\StreamAdapter;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
class MessageProtocolTest extends TestCase
{
    public function testDefaultVersion(): void
    {
        $p = (new Message(StreamAdapter::make()))->getProtocolVersion();

        self::assertIsString($p);
        self::assertEquals('1.1', $p);
    }

    public function testWithProtocol(): void
    {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withProtocolVersion('1.2');

        $p = $message->getProtocolVersion();
        self::assertIsString($p);
        self::assertEquals('1.1', $p);

        $p1 = $newMessage->getProtocolVersion();
        self::assertIsString($p1);
        self::assertEquals('1.2', $p1);

        self::assertNotSame($message, $newMessage);
    }
}
