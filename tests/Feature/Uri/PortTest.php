<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPort, withPort for '.Uri::class, function () {
    \it('Parse port through constructor', function (string $uri, ?int $port) {
        \expect((new Uri($uri))->getPort())->toBe($port);
    })->with([
        'empty URI' => ['', null],
        'no scheme without port' => ['//www.com/', null],
        'no scheme with port 80' => ['//www.com:80/', 80],
        'no scheme with port 443' => ['//www.com:443/', 443],
        'scheme "http" with port 80' => ['http://www.com:80/', null],
        'scheme "http" with port 8080' => ['http://www.com:8080/', 8080],
        'scheme "https" with port 443' => ['https://www.com:443', null],
        'scheme "https" with port 4444' => ['https://www.com:4444', 4444],
        'scheme "http" with port 0' => ['http://www.com:0', 0],
        'scheme "https" with port 0' => ['https://www.com:0', 0],
        'scheme "https" with port 65535' => ['https://www.com:65535', 65535],
        'scheme "http" and IP4 without port' => ['http://192.168.0.1/', null],
        'scheme "http" and IP4 with port 80' => ['http://192.168.0.1:80/', null],
        'scheme "http" and IP4 with port 81' => ['http://192.168.0.1:81/', 81],
        'scheme "https" and IP4 with port 443' => ['https://192.168.0.1:443/', null],
        'scheme "http" and IP4 with port 444' => ['http://192.168.0.1:444/', 444],
        'scheme "http" and IP6 without port' => ['http://[::1]/', null],
        'scheme "http" and IP6 with port 80' => ['http://[::1]:80/', null],
        'scheme "http" and IP6 with port 81' => ['http://[::1]:81/', 81],
        'scheme "https" and IP6 without port' => ['https://[::1]/', null],
        'scheme "https" and IP6 with port 443' => ['https://[::1]:443/', null],
        'scheme "http" and IP6 with port 444' => ['http://[::1]:444/', 444],
        'port is "1"' => ['https://0:0@0:1/0?0#0', 1],
    ]);

    \it('Parse port through constrictor with exception', function (string $uri) {
        new Uri($uri);
    })
        ->throws(InvalidArgumentException::class, 'Invalid URI')
        ->with([
            'scheme "https" with port 65536' => ['https://www.com:65536'],
            'scheme "http" with port 65536' => ['http://www.com:65536'],
            'scheme "http" with port -1' => ['http://www.com:-1'],
            'scheme "http" with port abc' => ['http://www.com:abc'],
            'scheme "https" with port abc' => ['https://www.com:abc'],
            'scheme "http" with IP4 port 66000' => ['http://192.168.0.1:66000'],
            'scheme "http" with IP6 port 66000' => ['http://[::1]:66000'],
        ])
    ;

    \it('Method withPort', function (Uri $uri, ?int $port, ?int $expectPort) {
        $new = $uri->withPort($port);

        \expect($new)->not->toBe($uri)
            ->and($new->getPort())->toBe($expectPort)
        ;
    })->with([
        'port is "1"' => [
            new Uri('https://0:0@0:0/0?0#0'),
            1,
            1,
        ],
        'scheme "http" port 80' => [
            new Uri('http://www.com'),
            80,
            null,
        ],
        'scheme "http" port 0' => [
            new Uri('http://www.com'),
            0,
            0,
        ],
        'scheme "http" port null' => [
            new Uri('http://www.com'),
            null,
            null,
        ],
        'scheme "http" port 8080' => [
            new Uri('http://www.com:8080'),
            80,
            null,
        ],
        'scheme "https" port 443' => [
            new Uri('https://www.com:444'),
            443,
            null,
        ],
        'scheme "https" IP4 port 444' => [
            new Uri('https://192.168.1.1'),
            444,
            444,
        ],
        'scheme "http" IP6 port 80' => [
            new Uri('http://[::1]:90'),
            80,
            null,
        ],
        'scheme "https" IP6 port 443' => [
            new Uri('https://[::1]'),
            443,
            null,
        ],
        'scheme "https" IP6 port 444' => [
            new Uri('https://[::1]'),
            444,
            444,
        ],
        'scheme "https" IP6 port 0' => [
            new Uri('https://[::1]'),
            0,
            0,
        ],
    ]);

    \it('Method withPort and exception', function (Uri $uri, int $port) {
        $uri->withPort($port);
    })
        ->throws(InvalidArgumentException::class, 'Invalid port')
        ->with([
            'scheme "http" port "-1"' => [new Uri('http://www.com'), -1],
            'scheme "https" port "65536"' => [new Uri('http://www.com'), 65536],
        ])
    ;
})
    ->covers(Uri::class)
;
