<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getQuery, withQuery for '.Uri::class, function () {
    \it('Parse query string through constructor', function (string $uri, string $query) {
        \expect((new Uri($uri))->getQuery())->toBe($query);
    })->with([
        'empty' => [
            'uri' => '',
            'query' => '',
        ],

        'has host and query' => [
            'uri' => 'localhost/?p1=10&x[]=10',
            'query' => 'p1=10&x%5B%5D=10',
        ],

        'no host but has query style string' => [
            'uri' => '?param=abc&host=10',
            'query' => 'param=abc&host=10',
        ],

        'with reserved symbols only' => [
            'uri' => 'http://www.com/?a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
            'expect' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
        ],

        'has uri without query string' => [
            'uri' => 'HTTPS://domain.com/index.html',
            'query' => '',
        ],
        'query is "0"' => [
            'uri' => 'https://0:0@0:1/0?0#0',
            'query' => '0',
        ],
    ]);

    \it('Method withQuery', function (Uri $uri, string $query, string $expect) {
        $new = $uri->withQuery($query);

        \expect($new)->not->toBe($uri)
            ->and($new->getQuery())->toBe($expect)
        ;
    })->with([
        'empty' => [
            'uri' => new Uri(''),
            'query' => '',
            'expect' => '',
        ],

        'exist uri and set empty query' => [
            'uri' => new Uri('https://www.com/document?abc=10&x[]=fix%20plan'),
            'query' => '',
            'expect' => '',
        ],

        'uri and set query unavailable symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'query' => 'param money[]=€',
            'expect' => 'param%20money%5B%5D=%E2%82%AC',
        ],

        'uri and set fragment available symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'query' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
            'expect' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
        ],
        'query is "0"' => [
            'uri' => new Uri(''),
            'query' => '0',
            'expect' => '0',
        ],
    ]);
})
    ->covers(Uri::class)
;
