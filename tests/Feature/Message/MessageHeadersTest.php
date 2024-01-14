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

    \it('Get header empty name', function () {
        (new Message())->getHeader('');
    })->throws(
        InvalidArgumentException::class,
        'Header name is empty string'
    );

    \it('Header values must be a non empty string', function () {
        (new Message())->withHeader('foo', ['Baz', "\t\t   \t", 'Bar']);
    })->throws(
        InvalidArgumentException::class,
        'Header values must be a non empty string'
    );

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
            ->and($newMessage->getHeaders())->toBe(['oka' => ['Foo', 'Bar']])
        ;

        $newSubMessage = $newMessage->withHeader('OKa', ['   Baz  Foo    ', 4567890]);

        \expect($newSubMessage)->not->toBe($newMessage)
            ->and($newSubMessage->getHeaders())->toBe(['oka' => ['Baz  Foo', '4567890']])
        ;
    });

    \it('method withoutHeader', function () {
        $message = (new Message())->withHeader('Bar', 'Baz');

        \expect($message->withoutHeader('x')->getHeaders())->toBe(['bar' => ['Baz']])
            ->and($message->withoutHeader('bar')->getHeaders())->toBe([])
        ;
    });

    \it('method withAddedHeader', function () {
        $message = (new Message())->withHeader('Bar', 'Baz');
        $newMessage = $message->withAddedHeader('bar', 'Foo');

        \expect($newMessage->getHeaders())->toBe(['bar' => ['Baz', 'Foo']]);

        $newSubMessage = $newMessage->withAddedHeader('REACT', '❤');

        \expect($newSubMessage->getHeaders())->toBe(['bar' => ['Baz', 'Foo'], 'react' => ['❤']]);
    });
})
    ->covers(Message::class)
;
