<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Psr\Http\Message\StreamInterface;

\describe('Method getBody, withBody for '.Message::class, function () {
    \it('method getBody', function () {
        $message = new Message();
        \expect($message->getBody())->toBeInstanceOf(StreamInterface::class)
            ->and($message->getBody())->toBeInstanceOf(Stream::class)
        ;
    });

    \it('method withBody', function () {
        $message = new Message();
        $newMessage = $message->withBody(new Stream(''));

        \expect($message)->not->toBe($newMessage);
    });
})
    ->covers(Message::class, Stream::class)
;
