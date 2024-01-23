<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Psr\Http\Message\StreamInterface;
use Tests\Kaspi\HttpMessage\StreamAdapter;

\describe('Method getBody, withBody for '.Message::class, function () {
    \it('method getBody', function () {
        $message = new Message(StreamAdapter::make());
        \expect($message->getBody())->toBeInstanceOf(StreamInterface::class)
            ->and($message->getBody())->toBeInstanceOf(Stream::class)
        ;
    });

    \it('method withBody', function () {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withBody(StreamAdapter::make());

        \expect($message)->not->toBe($newMessage);
    });
})
    ->covers(Message::class, Stream::class, CreateStreamFromStringTrait::class)
;
