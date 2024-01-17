<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;

\describe('Methods getHeaders, getHeader, getHeaderLine, withHeader, withAddedHeader, withoutHeader', function () {
    \it('empty headers', function () {
        \expect((new Message())->getHeaders())->toBe([]);
    });

    \it('no header by name', function () {
        \expect((new Message())->getHeader('ok'))->toBe([]);
    });

    \it('Method withHeader', function () {
        $message = new Message();
        $newMessage = $message->withHeader('ok', 123456);

        \expect($newMessage)->not->toBe($message)
            ->and($newMessage->getHeaders())->toBe(['ok' => ['123456']])
            ->and($newMessage->hasHeader('ok-no'))->toBeFalse()
            ->and($newMessage->getHeader('ok'))->toBe(['123456'])
            ->and($newMessage->getHeaderLine('ok'))->toBe('123456')
        ;

        \expect($message->getHeaders())->toBe([]);

        $newSubMessage = $message->withHeader('h', ['Foo', '  1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none']);

        \expect($newSubMessage)->not->toBe($message);

        \expect($newSubMessage->getHeaderLine('h'))
            ->toBe('Foo, 1P_JAR=2024-01-13-18; expires=Mon, 12-Feb-2024 18:01:08 GMT; path=/; domain=.google.com; Secure; SameSite=none')
        ;
    });

    \it('WithHeader update header values', function () {
        $message = new Message();
        $newMessage = $message->withHeader('OKa', [" \tFoo   \t", 'Bar']);

        \expect($newMessage)->not->toBe($message)
            ->and($newMessage->getHeaders())->toBe(['OKa' => ['Foo', 'Bar']])
        ;

        $newSubMessage = $newMessage->withHeader('OKa', ['   Baz  Foo    ', 4567890]);

        \expect($newSubMessage)->not->toBe($newMessage)
            ->and($newSubMessage->getHeaders())->toBe(['OKa' => ['Baz  Foo', '4567890']])
        ;
    });

    \it('method withoutHeader', function () {
        $message = (new Message())->withHeader('Bar', 'Baz');

        \expect($message->withoutHeader('x')->getHeaders())->toBe(['Bar' => ['Baz']])
            ->and($message->withoutHeader('Bar')->getHeaders())->toBe([])
        ;
    });

    \it('method withAddedHeader', function () {
        $message = (new Message())->withHeader('Bar', 'Baz');
        $newMessage = $message->withAddedHeader('bar', 'Foo');

        \expect($newMessage->getHeaders())->toBe(['Bar' => ['Baz', 'Foo']]);

        $newSubMessage = $newMessage->withAddedHeader('REACT', '❤');

        \expect($newSubMessage->getHeaders())->toBe(['Bar' => ['Baz', 'Foo'], 'REACT' => ['❤']]);
    });

    \it('method hasHeader, getHeader with header name "0"', function () {
        $message = (new Message())->withHeader('0', 'Baz');

        \expect($message->hasHeader('0'))->toBeTrue()
            ->and($message->hasHeader('false'))->toBeFalse()
            ->and($message->getHeader('0'))->toBe(['Baz'])
        ;

        $newMessage = $message->withAddedHeader('0', 'Foo');

        \expect($newMessage->hasHeader('0'))->toBeTrue()
            ->and($newMessage->getHeader('0'))->toBe(['Baz', 'Foo'])
        ;

        $subNewMessage = $newMessage->withHeader('1.2', 'Fiz');

        \expect($subNewMessage->hasHeader('1.2'))->toBeTrue()
            ->and($subNewMessage->getHeader('1.2'))->toBe(['Fiz'])
        ;

        $subSubNewMessage = $subNewMessage->withHeader('12', 'Viz');

        \expect($subSubNewMessage->hasHeader('12'))->toBeTrue()
            ->and($subSubNewMessage->getHeader('12'))->toBe(['Viz'])
            ->and($subSubNewMessage->getHeaders())
            ->toBe([
                '0' => ['Baz', 'Foo'],
                '1.2' => ['Fiz'],
                '12' => ['Viz'],
            ])
        ;
    });
})
    ->covers(Message::class)
;
