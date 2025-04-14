<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Tests\Kaspi\HttpMessage\StreamAdapter;

\describe('Exception for headers methods', function () {
    \it('Header name must be RFC 7230 compatible', function (string $name, mixed $value) {
        (new Message(StreamAdapter::make('')))->withHeader($name, $value);
    })->throws(
        InvalidArgumentException::class,
        'RFC 7230 compatible'
    )
        ->with([
            'header non ascii' => ['привет', null],
            'header as emoji' => ['💛', ['ok']],
            'value non valid x00' => ['h', [\chr(0)]],
            'value with ESC symbol' => ['h', \chr(27)],
            'value with bell symbol' => ['h', \chr(07)],
        ])
    ;

    \it('Header name or values is empty value', function ($name, $values) {
        (new Message(StreamAdapter::make('')))->withHeader($name, $values);
    })->throws(InvalidArgumentException::class)
        ->with([
            'empty header name' => ['', ['ok']],
            'empty array in values' => ['ok', []],
        ])
    ;

    \it('Get header empty name', function () {
        (new Message(StreamAdapter::make('')))->getHeader('');
    })->throws(
        InvalidArgumentException::class,
        'Header name is empty string'
    );
})->covers(Message::class, Stream::class, CreateStreamFromStringTrait::class);
