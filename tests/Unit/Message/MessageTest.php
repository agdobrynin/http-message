<?php

declare(strict_types=1);

namespace Message;

use Kaspi\HttpMessage\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    public static function dataWithHeaderException(): \Generator
    {
        yield 'empty' => [
            'name' => '',
            'value' => '',
            'message' => 'Header name is empty string',
        ];

        yield 'empty value' => [
            'name' => 'h',
            'value' => '',
            'message' => 'Header values must be non empty string',
        ];

        yield 'empty array' => [
            'name' => 'h',
            'value' => ['', '', ''],
            'message' => 'Header values must be non empty string',
        ];

        yield 'header non ascii' => [
            'name' => 'привет',
            'value' => null,
            'message' => 'Header name must be RFC 7230 compatible',
        ];

        yield 'header as emoji' => [
            'name' => '💛',
            'value' => ['ok'],
            'message' => 'Header name must be RFC 7230 compatible',
        ];
    }

    /**
     * @dataProvider dataWithHeaderException
     */
    public function testWithHeaderException(string $name, mixed $value, string $message): void
    {
        $m = new Message();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $m->withHeader($name, $value);
    }
}
