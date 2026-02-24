<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Message;

use Generator;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\StreamAdapter;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
class MessageHeadersTest extends TestCase
{
    public function testEmptyHeaders(): void
    {
        self::assertEquals([], (new Message(StreamAdapter::make()))->getHeaders());
    }

    public function testNoHeaderByName(): void
    {
        self::assertEquals([], (new Message(StreamAdapter::make()))->getHeader('ok'));
    }

    public function testMethodWithHeader(): void
    {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withHeader('ok', 123456);

        self::assertNotSame($message, $newMessage);
        self::assertEquals(['ok' => ['123456']], $newMessage->getHeaders());
        self::assertFalse($newMessage->hasHeader('ok-no'));
        self::assertEquals(['123456'], $newMessage->getHeader('ok'));
        self::assertEquals('123456', $newMessage->getHeaderLine('ok'));
        self::assertEquals([], $message->getHeaders());

        $newSubMessage = $message->withHeader('h', ['Foo', '  1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none']);

        self::assertNotSame($message, $newSubMessage);
        self::assertEquals('Foo, 1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none', $newSubMessage->getHeaderLine('h'));
    }

    public function testWithHeaderUpdateHeaderValues(): void
    {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withHeader('OKa', [" \tFoo   \t", 'Bar']);

        self::assertNotSame($message, $newMessage);
        self::assertEquals(['OKa' => ['Foo', 'Bar']], $newMessage->getHeaders());

        $newSubMessage = $newMessage->withHeader('OKa', ['   Baz  Foo    ', 4567890]);

        self::assertNotSame($message, $newSubMessage);
        self::assertEquals(['OKa' => ['Baz  Foo', '4567890']], $newSubMessage->getHeaders());
    }

    public function testMethodWithoutHeader(): void
    {
        $message = (new Message(StreamAdapter::make()))->withHeader('Bar', 'Baz');

        self::assertEquals(['Bar' => ['Baz']], $message->withoutHeader('x')->getHeaders());
        self::assertEquals([], $message->withoutHeader('Bar')->getHeaders());
    }

    public function testMethodWithAddedHeader(): void
    {
        $message = (new Message(StreamAdapter::make()))->withHeader('Bar', 'Baz');
        $newMessage = $message->withAddedHeader('bar', 'Foo');

        self::assertEquals(['Bar' => ['Baz', 'Foo']], $newMessage->getHeaders());

        $newSubMessage = $newMessage->withAddedHeader('REACT', '❤');

        self::assertEquals(['Bar' => ['Baz', 'Foo'], 'REACT' => ['❤']], $newSubMessage->getHeaders());
    }

    public function testMethodHasHeaderAndHeaderNameNull(): void
    {
        $message = (new Message(StreamAdapter::make()))->withHeader('0', 'Baz');

        self::assertTrue($message->hasHeader('0'));
        self::assertFalse($message->hasHeader('false'));
        self::assertEquals(['Baz'], $message->getHeader('0'));

        $newMessage = $message->withAddedHeader('0', 'Foo');

        self::assertTrue($newMessage->hasHeader('0'));
        self::assertEquals(['Baz', 'Foo'], $newMessage->getHeader('0'));

        $subNewMessage = $newMessage->withHeader('1.2', 'Fiz');

        self::assertTrue($subNewMessage->hasHeader('1.2'));
        self::assertEquals(['Fiz'], $subNewMessage->getHeader('1.2'));

        $subSubNewMessage = $subNewMessage->withHeader('12', 'Viz');

        self::assertTrue($subSubNewMessage->hasHeader('12'));
        self::assertEquals(['Viz'], $subSubNewMessage->getHeader('12'));
        self::assertEquals(
            [
                '0' => ['Baz', 'Foo'],
                '1.2' => ['Fiz'],
                '12' => ['Viz'],
            ],
            $subSubNewMessage->getHeaders(),
        );
    }

    #[DataProvider('dataProviderValuesInHeaderEmptyValue')]
    public function testValuesInHeaderEmptyValue(string $name, mixed $value, array $expect): void
    {
        $m = (new Message(StreamAdapter::make()))->withHeader($name, $value);

        self::assertEquals($expect, $m->getHeader($name));
    }

    public static function dataProviderValuesInHeaderEmptyValue(): Generator
    {
        yield 'empty value' => ['h', '', ['']];

        yield 'empty array' => ['h', ['', '', ''], ['', '', '']];
    }

    public function testHeaderValuesMaybeEmptyString(): void
    {
        self::assertEquals(
            ['Baz', '', 'Bar'],
            (new Message(StreamAdapter::make()))
                ->withHeader('foo', ['Baz', "\t\t   \t", 'Bar'])
                ->getHeader('foo')
        );
    }
}
