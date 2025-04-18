<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;

\describe('Methods of '.ServerRequest::class, function () {
    \it('getCookieParams, withCookieParams', function () {
        \expect(($sr = new ServerRequest())->getCookieParams())->toBe([])
            ->and(
                $sr2 = $sr->withCookieParams(['q' => 'post', ['x' => [1, 2]]])
            )->not->toBe($sr)
            ->and($sr2->getCookieParams())->toBe(['q' => 'post', ['x' => [1, 2]]])
        ;
    });

    \it('getQueryParams, withQueryParams', function () {
        \expect(($sr = new ServerRequest())->getQueryParams())->toBe([])
            ->and(
                $sr2 = $sr->withQueryParams(['q' => 'post', ['x' => [1, 2]]])
            )->not->toBe($sr)
            ->and($sr2->getQueryParams())->toBe(['q' => 'post', ['x' => [1, 2]]])
        ;
    });

    \it('success call getParsedBody, withParsedBody', function ($parsedBody) {
        \expect(($sr = new ServerRequest())->getParsedBody())->toBeNull()
            ->and(
                $sr2 = $sr->withParsedBody($parsedBody)
            )->not->toBe($sr)
            ->and($sr2->getParsedBody())->toBe($parsedBody)
        ;
    })
        ->with([
            'null' => [null],
            'array' => [['hello' => 'world']],
            'object' => [(object) ['hello' => 'world']],
            'object as class' => [new stdClass()],
        ])
    ;

    \it('fail call withParsedBody', function ($parsedBody) {
        (new ServerRequest())->withParsedBody($parsedBody);
    })
        ->throws(InvalidArgumentException::class)
        ->with([
            'int' => [10],
            'string' => ['Hi!'],
            'float' => [3.14],
            'boolean' => [false],
            'resource' => [\fopen(vfsStream::newFile('f')->at(vfsStream::setup())->url(), 'rb')],
        ])
    ;

    \it('getAttributes, getAttribute, withAttribute, withoutAttribute', function () {
        $sr = new ServerRequest();
        $name = 'hello';
        $value = ['world', 'php'];

        \expect($sr->getAttributes())->toBe([])
            ->and($sr->getAttribute('no-attr', 100))->toBe(100)
            ->and($sr2 = $sr->withAttribute($name, $value))->not->toBe($sr)
            ->and($sr2->getAttribute($name))->toBe($value)
            ->and($sr3 = $sr2->withoutAttribute($name))->not->toBe($sr2)
            ->and($sr3->getAttributes())->toBe([])
        ;
    });
})
    ->covers(
        ServerRequest::class,
        Message::class,
        Request::class,
        Stream::class,
        Uri::class,
        FileStream::class,
    )
;
