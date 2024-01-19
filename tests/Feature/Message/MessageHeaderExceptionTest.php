<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;

\describe('Exception for headers methods', function () {
    \it('Header name must be RFC 7230 compatible', function (string $name, mixed $value) {
        (new Message())->withHeader($name, $value);
    })->throws(
        InvalidArgumentException::class,
        'RFC 7230 compatible'
    )
        ->with([
            'header non ascii' => ['name' => 'привет', 'value' => null],
            'header as emoji' => ['name' => '💛', 'value' => ['ok']],
            'value non valid x00' => ['name' => 'h', 'value' => [\chr(0)]],
            'value with ESC symbol' => ['name' => 'h', 'value' => \chr(27)],
            'value with bell symbol' => ['name' => 'h', 'value' => \chr(07)],
        ])
    ;

    \it('Header name or values is empty value', function ($name, $values) {
        (new Message())->withHeader($name, $values);
    })->throws(InvalidArgumentException::class)
        ->with([
            'empty header name' => ['', ['ok']],
            'empty array in values' => ['ok', []],
        ])
    ;

    \it('Get header empty name', function () {
        (new Message())->getHeader('');
    })->throws(
        InvalidArgumentException::class,
        'Header name is empty string'
    );
})->covers(Message::class, Stream::class);
