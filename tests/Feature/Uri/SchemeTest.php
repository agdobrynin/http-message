<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPort, withPort for '.Uri::class, function () {
    \it('Parse scheme through constructor', function (string $uri, string $scheme) {
        \expect((new Uri($uri))->getScheme())->toBe($scheme);
    })->with([
        'empty URI' => ['', ''],
        'string URI' => ['ww.site.com', ''],
        'scheme "https"' => ['HTTPS://MY.NET/', 'https'],
        'scheme "http"' => ['HttP://user@DOMAIN/', 'http'],
        'scheme "news"' => ['NEws://RELCOME.NET/', 'news'],
        'scheme "https" URI IP4 with port' => ['HTTPS://192.168.1.1:90/', 'https'],
        'scheme "https" URI IP6 with port' => ['HTTPS://[::1]:1025/', 'https'],
    ]);

    \it('Method withScheme', function (Uri $uri, string $scheme, string $expect) {
        $new = $uri->withScheme($scheme);

        \expect($new)->not->toBe($uri)
            ->and($new->getScheme())->toBe($expect)
        ;
    })->with([
        'Scheme empty change scheme https' => [
            new Uri('//www.yahoo.com'),
            'Https',
            'https',
        ],

        'Scheme empty change scheme http' => [
            new Uri('//www.yahoo.com'),
            'HttP',
            'http',
        ],

        'Scheme http change to empty' => [
            new Uri('http://www.yahoo.com'),
            '',
            '',
        ],
    ]);

    \it('Scheme must be a string', function ($scheme) {
        (new Uri(''))->withScheme($scheme);
    })
        ->throws(InvalidArgumentException::class)
        ->with([
            [true],
            [false],
            [70],
            [[]],
            [(object) []],
            [new stdClass()],
            [new Uri('')],
        ])
    ;
})
    ->covers(Uri::class)
;
