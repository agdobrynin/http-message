<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
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
})->covers(Message::class, Stream::class);
