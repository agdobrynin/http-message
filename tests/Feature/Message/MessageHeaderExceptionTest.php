<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;

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

    \it('Header name empty value', function () {
        (new Message())->withHeader('', ['ok']);
    })->throws(
        InvalidArgumentException::class,
        'Header name is empty string'
    );

    \it('Values in header empty value', function (string $name, mixed $value) {
        (new Message())->withHeader($name, $value);
    })->throws(
        InvalidArgumentException::class,
        'Header values must be non empty string'
    )
        ->with([
            'empty value' => ['name' => 'h', 'value' => ''],
            'empty array' => ['name' => 'h', 'value' => ['', '', '']],
        ])
    ;
})->covers(Message::class);
