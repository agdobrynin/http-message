<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getHost, withHost for '.Uri::class, function () {
    \it('Method getHost', function (Uri $uri, string $host) {
        \expect($uri->getHost())->toBe($host);
    })->with([
        'empty URI' => [fn () => new Uri(''), ''],
        'string URI without scheme' => [fn () => new Uri('ww.site.com'), ''],
        'without scheme URI with big letter' => [fn () => new Uri('//WWW.cOM/aaaa?anc=1'), 'www.com'],
        'scheme "https" URI with big letter' => [fn () => new Uri('https://my.NET/?anc=1'), 'my.net'],
        'scheme "http" URI host short' => [fn () => new Uri('http://DOMain/'), 'domain'],
        'scheme "https" URI IP4 with port' => [fn () => new Uri('https://192.168.1.1:90/'), '192.168.1.1'],
        'scheme "https" URI IP6 with port' => [fn () => new Uri('https://[::1]:1025/'), '[::1]'],
        'host is "0"' => [fn () => new Uri('https://0:0@0:1/0?0#0'), '0'],
    ]);

    \it('Method witHost', function (Uri $uri, string $host, string $expect) {
        $new = $uri->withHost($host);
        \expect($new)->not->toBe($uri)
            ->and($new->getHost())->toBe($expect)
        ;
    })->with([
        'Scheme empty change host' => [
            new Uri('//www.yahoo.com'),
            'www.NEWS.com',
            'www.news.com',
        ],
        'Scheme empty change host to "0"' => [
            new Uri('//www.yahoo.com'),
            '0',
            '0',
        ],
        'Scheme empty change to IP4' => [
            new Uri('//www.yahoo.com'),
            '8.8.8.8',
            '8.8.8.8',
        ],

        'Scheme empty change to IP6' => [
            new Uri('//www.yahoo.com'),
            '[2001:0DB8:11A3:09D7:1F34:8A2E:07A0:765D]',
            '[2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d]',
        ],

        'Scheme "https" change to local domain' => [
            new Uri('https://www.yahoo.com'),
            'LOCALHOST',
            'localhost',
        ],

        'Scheme "http" empty host' => [
            new Uri('https://www.yahoo.com'),
            '',
            '',
        ],

        'Scheme "https" host internationally' => [
            new Uri('https://xn--80aiifkqki.xn--p1ai/'),
            'XN--H1AHN.XN--P1AI',
            'xn--h1ahn.xn--p1ai',
        ],

        'host cyrillic utf-8 - non ASCII symbols not convert to lowercase' => [
            new Uri(''),
            'Мир.РФ',
            'Мир.РФ',
        ],
    ]);
})
    ->covers(Uri::class)
;
