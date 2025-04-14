<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getQuery, withQuery for '.Uri::class, function () {
    \it('Parse query string through constructor', function (string $uri, string $query) {
        \expect((new Uri($uri))->getQuery())->toBe($query);
    })->with([
        'empty' => [
            '',
            '',
        ],

        'has host and query' => [
            'localhost/?p1=10&x[]=10',
            'p1=10&x%5B%5D=10',
        ],

        'no host but has query style string' => [
            '?param=abc&host=10',
            'param=abc&host=10',
        ],

        'with reserved symbols only' => [
            'http://www.com/?a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
        ],

        'has uri without query string' => [
            'HTTPS://domain.com/index.html',
            '',
        ],
        'query is "0"' => [
            'https://0:0@0:1/0?0#0',
            '0',
        ],
    ]);

    \it('Method withQuery', function (Uri $uri, string $query, string $expect) {
        $new = $uri->withQuery($query);

        \expect($new)->not->toBe($uri)
            ->and($new->getQuery())->toBe($expect)
        ;
    })->with([
        'empty' => [
            new Uri(''),
            '',
            '',
        ],

        'exist uri and set empty query' => [
            new Uri('https://www.com/document?abc=10&x[]=fix%20plan'),
            '',
            '',
        ],

        'uri and set query unavailable symbols' => [
            new Uri('https://www.com/document'),
            'param money[]=€',
            'param%20money%5B%5D=%E2%82%AC',
        ],

        'uri and set fragment available symbols' => [
            new Uri('https://www.com/document'),
            'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
            'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
        ],
        'query is "0"' => [
            new Uri(''),
            '0',
            '0',
        ],
    ]);
})
    ->covers(Uri::class)
;
