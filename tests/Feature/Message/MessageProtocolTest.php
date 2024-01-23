<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use Tests\Kaspi\HttpMessage\StreamAdapter;

\describe('Methods getProtocolVersion, withProtocolVersion for '.Message::class, function () {
    \it('default version', function () {
        \expect((new Message(StreamAdapter::make()))->getProtocolVersion())->toBeString()
            ->toBe('1.1')
        ;
    });

    \it('withProtocol', function () {
        $message = new Message(StreamAdapter::make());
        $newMessage = $message->withProtocolVersion('1.2');

        \expect($newMessage->getProtocolVersion())->toBeString()
            ->toBe('1.2')
        ;

        \expect($message->getProtocolVersion())->toBeString()
            ->toBe('1.1')
        ;

        \expect($message)->not->toBe($newMessage);
    });
})
    ->covers(Message::class, Stream::class, CreateStreamFromStringTrait::class)
;
