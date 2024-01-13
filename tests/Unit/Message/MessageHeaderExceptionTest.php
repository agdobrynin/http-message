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
class MessageHeaderExceptionTest extends TestCase
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

        yield 'value non valid' => [
            'name' => 'h',
            'value' => ['"'],
            'message' => 'Header value must be RFC 7230 compatible. Got: \'"\'',
        ];

        yield 'value non valid backslash' => [
            'name' => 'h',
            'value' => ['\\'],
            'message' => 'Header value must be RFC 7230 compatible',
        ];

        yield 'value with ESC symbol' => [
            'name' => 'h',
            'value' => \chr(27),
            'message' => 'Header value must be RFC 7230 compatible',
        ];

        yield 'value with bell symbol' => [
            'name' => 'h',
            'value' => \chr(07),
            'message' => 'Header value must be RFC 7230 compatible',
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
