<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;

\describe('Methods getProtocolVersion, withProtocolVersion for '.Message::class, function () {
    \it('default version', function () {
        \expect((new Message())->getProtocolVersion())->toBeString()
            ->toBe('1.1')
        ;
    });

    \it('withProtocol', function () {
        $message = new Message();
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
        (new Message())->withProtocolVersion('1');
    })->throws(InvalidArgumentException::class);
})
    ->covers(Message::class)
;
