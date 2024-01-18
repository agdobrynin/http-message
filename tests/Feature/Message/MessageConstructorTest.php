<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;

\describe('Message constructor of '.Message::class, function () {
    \it('Empty constructor', function () {
        \expect($m = new Message())
            ->and($m->getBody())->toBeInstanceOf(StreamInterface::class)
            ->and($m->getBody()->getSize())->toBe(0)
            ->and((string) $m->getBody())->toBe('')
            ->and($m->getProtocolVersion())->toBe('1.1')
            ->and($m->getHeaders())->toBe([])
        ;
    });

    \it('Body', function ($body, $contents) {
        \expect((string) (new Message(body: $body))->getBody())->toBe($contents);
    })->with([
        'from string' => ['hello', 'hello'],
        'from StreamInterface' => [new Stream('welcome to class'), 'welcome to class'],
        'from resource' => [
            \fopen(vfsStream::newFile('x.txt')->setContent('Virtual file!')->at(vfsStream::setup())->url(), 'rb'),
            'Virtual file!',
        ],
    ]);

    \it('Body wrong type', function ($body) {
        new Message(body: $body);
    })
        ->throws(InvalidArgumentException::class, 'Argument must be type "resource" or "string"')
        ->with([
            'object' => ['body' => (object) ['aaaa']],
            'float' => ['body' => 1.234],
            'int' => ['body' => 0xFF],
            'array' => ['body' => []],
            'class' => ['body' => new Message()],
        ])
    ;

    \it('Protocol version', function ($version) {
        \expect((new Message(protocolVersion: $version))->getProtocolVersion())->toBe($version);
    })
        ->with(['2.0', '1.0', '2.12'])
    ;

    \it('Protocol version wrong', function ($version) {
        new Message(protocolVersion: $version);
    })
        ->throws(InvalidArgumentException::class, 'Protocol must be implement')
        ->with(['', 'a', '2', '0.x'])
    ;

    \it('Headers', function ($headers, $expectHeaders) {
        \expect((new Message(headers: $headers))->getHeaders())->toBe($expectHeaders);
    })
        ->with([
            'set # 1' => [
                'headers' => ['content-type' => ['plain/text', 'undefined-type']],
                'expectHeaders' => ['content-type' => ['plain/text', 'undefined-type']],
            ],

            'set # 2' => [
                'headers' => ['content-type' => 'undefined-type'],
                'expectHeaders' => ['content-type' => ['undefined-type']],
            ],
        ])
    ;

    \it('Headers wrong', function ($headers, $exceptionMessage) {
        $this->expectExceptionMessage($exceptionMessage);
        new Message(headers: $headers);
    })
        ->throws(InvalidArgumentException::class)
        ->with([
            'set # 1' => [
                'headers' => ['content type' => ['plain/text', 'undefined-type']],
                'exceptionMessage' => 'Header name must be RFC 7230 compatible',
            ],
            'set # 2' => [
                'headers' => ['❤' => ['plain/text', 'undefined-type']],
                'exceptionMessage' => 'Header name must be RFC 7230 compatible',
            ],
            'set # 3' => [
                'headers' => ['[ok]' => ['plain/text', 'undefined-type']],
                'exceptionMessage' => 'Header name must be RFC 7230 compatible',
            ],
            'set # 4' => [
                'headers' => ['файл' => ['plain/text', 'undefined-type']],
                'exceptionMessage' => 'Header name must be RFC 7230 compatible',
            ],
            'set # 5' => [
                'headers' => ['content-type' => (object) ['v' => 1]],
                'exceptionMessage' => 'Header value must be RFC 7230 compatible',
            ],
            'set # 6' => [
                'headers' => ['content-type' => [['v' => 1]]],
                'exceptionMessage' => 'Header value must be RFC 7230 compatible',
            ],
            'set # 7' => [
                'headers' => ['content-type' => \chr(8)],
                'exceptionMessage' => 'Header value must be RFC 7230 compatible',
            ],
        ])
    ;
})->covers(Message::class, Stream::class);
