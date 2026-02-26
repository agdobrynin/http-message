<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Message;

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Tests\Kaspi\HttpMessage\StreamAdapter;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
class MessageBodyTest extends TestCase
{
    public function testMethodGetBody(): void
    {
        $message = new Message(StreamAdapter::make());

        self::assertInstanceOf(StreamInterface::class, $message->getBody());
        self::assertInstanceOf(Stream::class, $message->getBody());
    }

    public function testMethodWithBody(): void
    {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withBody(StreamAdapter::make());

        self::assertNotSame($newMessage, $message);
    }
}
