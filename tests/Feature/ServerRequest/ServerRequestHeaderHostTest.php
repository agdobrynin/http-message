<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Uri;

\describe('Host header', function () {
    // @see https://www.php-fig.org/psr/psr-7/#host-header

    \it('header is empty, uri is empty, withUri empty', function () {
        $sr = (new ServerRequest(
            uri: new Uri('')
        ))->withUri(new Uri(''), true);

        \expect($sr->getHeaderLine('Host'))->toBe('');
    });

    \it('header is empty, uri has host, withUri is empty', function () {
        $sr = (new ServerRequest(
            uri: new Uri('https://foo.com')
        )
        )->withUri(new Uri(''), true);

        \expect($sr->getHeaderLine('Host'))->toBe('foo.com');
    });

    \it('header is empty, uri has host, withUri has host', function () {
        $sr = (new ServerRequest(
            uri: new Uri('https://foo.com')
        ))->withUri(new Uri('https://bar.com'), true);

        \expect($sr->getHeaderLine('Host'))->toBe('foo.com');
    });

    \it('header has host, uri is empty, withUri has host', function () {
        $sr = (new ServerRequest(
            uri: new Uri(''),
            headers: ['Host' => ['foo.com']]
        ))->withUri(new Uri('https://bar.com'), true);

        \expect($sr->getHeaderLine('Host'))->toBe('foo.com');
    });

    \it('header has host, uri has host, withUri has host', function () {
        $sr = (new ServerRequest(
            uri: new Uri('https://bar.com'),
            headers: ['Host' => ['foo.com']]
        ))->withUri(new Uri('https://baz.com'), true);

        \expect($sr->getHeaderLine('Host'))->toBe('foo.com');
    });
})->covers(ServerRequest::class, Uri::class, Message::class, Request::class);
