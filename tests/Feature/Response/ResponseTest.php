<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\Stream;

\describe('Test class '.Response::class, function () {
    \it('Constructor code and status', function () {
        \expect(($r = new Response())->getStatusCode())->toBe(200)
            ->and($r->getReasonPhrase())->toBe('OK')
        ;

        \expect(($r = new Response(201, 'will be ok'))->getStatusCode())->toBe(201)
            ->and($r->getReasonPhrase())->toBe('will be ok')
        ;
    });

    \it('Fail status code in constructor', function (int $code) {
        \expect(new Response($code));
    })
        ->throws(InvalidArgumentException::class, 'Invalid status code')
        ->with([99, 600])
    ;

    \it('Fail with status code', function (int $code) {
        (new Response())->withStatus($code);
    })
        ->throws(InvalidArgumentException::class, 'Invalid status code')
        ->with([99, 600])
    ;

    \it('With status code', function () {
        \expect(($r = new Response())->getStatusCode())->toBe(200)
            ->and($r->getReasonPhrase())->toBe('OK')
            ->and($r1 = $r->withStatus(201))->not->toBe($r)
            ->and($r1->getStatusCode())->toBe(201)
            ->and($r1->getReasonPhrase())->toBe('Created')
        ;

        \expect($r2 = $r1->withStatus(404, 'Sorry i am not found your document right now'))
            ->not->toBe($r1)
            ->and($r2->getStatusCode())->toBe(404)
            ->and($r2->getReasonPhrase())->toBe('Sorry i am not found your document right now')
        ;
    });
})->covers(Response::class, Message::class, Stream::class);
