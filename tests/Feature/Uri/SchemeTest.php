<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPort, withPort for '.Uri::class, function () {
    \it('Parse scheme through constructor', function (string $uri, string $scheme) {
        \expect((new Uri($uri))->getScheme())->toBe($scheme);
    })->with([
        'empty URI' => ['uri' => '', 'scheme' => ''],
        'string URI' => ['uri' => 'ww.site.com', 'scheme' => ''],
        'scheme "https"' => ['uri' => 'HTTPS://MY.NET/', 'scheme' => 'https'],
        'scheme "http"' => ['uri' => 'HttP://user@DOMAIN/', 'scheme' => 'http'],
        'scheme "news"' => ['uri' => 'NEws://RELCOME.NET/', 'scheme' => 'news'],
        'scheme "https" URI IP4 with port' => ['uri' => 'HTTPS://192.168.1.1:90/', 'scheme' => 'https'],
        'scheme "https" URI IP6 with port' => ['uri' => 'HTTPS://[::1]:1025/', 'scheme' => 'https'],
    ]);

    \it('Method withScheme', function (Uri $uri, string $scheme, string $expect) {
        $new = $uri->withScheme($scheme);

        \expect($new)->not->toBe($uri)
            ->and($new->getScheme())->toBe($expect)
        ;
    })->with([
        'Scheme empty change scheme https' => [
            'uri' => new Uri('//www.yahoo.com'),
            'scheme' => 'Https',
            'expect' => 'https',
        ],

        'Scheme empty change scheme http' => [
            'uri' => new Uri('//www.yahoo.com'),
            'scheme' => 'HttP',
            'expect' => 'http',
        ],

        'Scheme http change to empty' => [
            'uri' => new Uri('http://www.yahoo.com'),
            'scheme' => '',
            'expect' => '',
        ],
    ]);
})
    ->covers(Uri::class)
;
