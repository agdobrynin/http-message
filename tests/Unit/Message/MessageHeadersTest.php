<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Unit\Message;

use Kaspi\HttpMessage\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Message::class)]
class MessageHeadersTest extends TestCase
{
    public function testGetHeadersEmpty(): void
    {
        $this->assertEmpty((new Message())->getHeaders());
    }

    public function testGetHeaderEmpty(): void
    {
        $this->assertEmpty((new Message())->getHeader('ok'));
    }

    public function testGetHeaderException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name is empty string');

        (new Message())->getHeader('');
    }

    public function testGetHeaderEmptyValueInHeaderException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header values must be a non empty string');

        (new Message())->withHeader('foo', ['Baz', "\t\t   \t", 'Bar']);
    }

    public function testWithHeader(): void
    {
        $message = new Message();
        $newMessage = $message->withHeader('ok', 123456);

        $this->assertNotSame($message, $newMessage);
        $this->assertEquals(['ok' => ['123456']], $newMessage->getHeaders());
        $this->assertEmpty($message->getHeaders());
        $this->assertFalse($newMessage->hasHeader('ok-no'));
        $this->assertEmpty($newMessage->getHeader('ok-no'));
        $this->assertEquals(['123456'], $newMessage->getHeader('ok'));
        $this->assertEquals('123456', $newMessage->getHeaderLine('ok'));

        $newSubMessage = $message->withHeader('h', ['Foo', '  1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none']);
        $this->assertEquals('Foo, 1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none', $newSubMessage->getHeaderLine('h'));
    }

    public function testWithHeaderWithExistHeader(): void
    {
        $message = new Message();
        $newMessage = $message->withHeader('OKa', [" \tFoo   \t", 'Bar']);

        $this->assertNotSame($message, $newMessage);
        $this->assertEquals(['oka' => ['Foo', 'Bar']], $newMessage->getHeaders());

        $newSubMessage = $newMessage->withHeader('OKa', ['   Baz  Foo    ', 4567890]);
        $this->assertEquals(['oka' => ['Baz  Foo', '4567890']], $newSubMessage->getHeaders());
    }

    public function testWithoutHeader(): void
    {
        $message = (new Message())->withHeader('Bar', 'Baz');

        $this->assertEquals(['bar' => ['Baz']], $message->withoutHeader('x')->getHeaders());
        $this->assertEmpty($message->withoutHeader('bar')->getHeaders());
    }

    public function testWithAddedHeader(): void
    {
        $message = (new Message())->withHeader('Bar', 'Baz');
        $newMessage = $message->withAddedHeader('bar', 'Foo');

        $this->assertEquals(['bar' => ['Baz', 'Foo']], $newMessage->getHeaders());

        $newSubMessage = $newMessage->withAddedHeader('REACT', '❤');

        $this->assertEquals(['bar' => ['Baz', 'Foo'], 'react' => ['❤']], $newSubMessage->getHeaders());
    }
}
