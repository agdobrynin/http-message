<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;
use Tests\Kaspi\HttpMessage\StreamAdapter;

\describe('Message constructor of '.Message::class, function () {
    \it('Empty constructor', function () {
        \expect($m = new Message(StreamAdapter::make()))
            ->and($m->getBody())->toBeInstanceOf(StreamInterface::class)
            ->and($m->getBody()->getSize())->toBe(0)
            ->and((string) $m->getBody())->toBe('')
            ->and($m->getProtocolVersion())->toBe('1.1')
            ->and($m->getHeaders())->toBe([])
        ;
    });

    \it('Body', function ($body, $contents) {
        \expect((string) (new Message(body: $body))->getBody())->toBe($contents);
    })
        ->with('message_body_success')
        ->with([
            'from resource' => [
                new Stream(\fopen(vfsStream::newFile('f')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb')),
                'Virtual file!',
            ],
        ])
    ;

    \it('Body wrong type', function ($body) {
        new Message(body: $body);
    })
        ->throws(TypeError::class)
        ->with('message_body_wrong')
    ;

    \it('Protocol version', function ($version) {
        \expect((new Message(StreamAdapter::make(), protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with('protocol_success')
    ;

    \it('Headers success', function ($headers, $expectHeaders) {
        \expect((new Message(StreamAdapter::make(), headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_success')
    ;

    \it('Headers wrong', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new Message(StreamAdapter::make(), headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with('headers_wrong')
    ;
})->covers(Message::class, Stream::class, CreateStreamFromStringTrait::class, PhpTempStream::class);
