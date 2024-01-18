<?php

declare(strict_types=1);

use Kaspi\HttpMessage\HttpFactory;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Uri;
use Psr\Http\Message\RequestInterface;

\describe('Test for '.HttpFactory::class, function () {
    \it('createRequest', function ($method, $uri) {
        \expect($r = (new HttpFactory())->createRequest($method, $uri))
            ->toBeInstanceOf(RequestInterface::class)
            ->and($r->getMethod())->toBe($method)
            ->and((string) $r->getUri())->toBe($uri)
        ;
    })
        ->with([
            'set #1' => [
                'method' => 'POST',
                'uri' => '',
            ],
        ])
    ;
})
    ->covers(
        HttpFactory::class,
        Message::class,
        Request::class,
        Stream::class,
        Uri::class,
    );
