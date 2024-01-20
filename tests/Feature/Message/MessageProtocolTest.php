<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateResourceFromStringTrait;
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

    \it('withProtocol has exception', function () {
        (new Message(StreamAdapter::make()))->withProtocolVersion('1');
    })->throws(InvalidArgumentException::class);
})
    ->covers(Message::class, Stream::class, CreateResourceFromStringTrait::class)
;
