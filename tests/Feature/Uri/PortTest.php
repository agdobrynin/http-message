<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPort, withPort for '.Uri::class, function () {
    \it('Parse port through constructor', function (string $uri, ?int $port) {
        \expect((new Uri($uri))->getPort())->toBe($port);
    })->with([
        'empty URI' => ['uri' => '', 'port' => null],
        'no scheme without port' => ['uri' => '//www.com/', 'port' => null],
        'no scheme with port 80' => ['uri' => '//www.com:80/', 'port' => 80],
        'no scheme with port 443' => ['uri' => '//www.com:443/', 'port' => 443],
        'scheme "http" with port 80' => ['uri' => 'http://www.com:80/', 'port' => null],
        'scheme "http" with port 8080' => ['uri' => 'http://www.com:8080/', 'port' => 8080],
        'scheme "https" with port 443' => ['uri' => 'https://www.com:443', 'port' => null],
        'scheme "https" with port 4444' => ['uri' => 'https://www.com:4444', 'port' => 4444],
        'scheme "http" with port 0' => ['uri' => 'http://www.com:0', 'port' => 0],
        'scheme "https" with port 0' => ['uri' => 'https://www.com:0', 'port' => 0],
        'scheme "https" with port 65535' => ['uri' => 'https://www.com:65535', 'port' => 65535],
        'scheme "http" and IP4 without port' => ['uri' => 'http://192.168.0.1/', 'port' => null],
        'scheme "http" and IP4 with port 80' => ['uri' => 'http://192.168.0.1:80/', 'port' => null],
        'scheme "http" and IP4 with port 81' => ['uri' => 'http://192.168.0.1:81/', 'port' => 81],
        'scheme "https" and IP4 with port 443' => ['uri' => 'https://192.168.0.1:443/', 'port' => null],
        'scheme "http" and IP4 with port 444' => ['uri' => 'http://192.168.0.1:444/', 'port' => 444],
        'scheme "http" and IP6 without port' => ['uri' => 'http://[::1]/', 'port' => null],
        'scheme "http" and IP6 with port 80' => ['uri' => 'http://[::1]:80/', 'port' => null],
        'scheme "http" and IP6 with port 81' => ['uri' => 'http://[::1]:81/', 'port' => 81],
        'scheme "https" and IP6 without port' => ['uri' => 'https://[::1]/', 'port' => null],
        'scheme "https" and IP6 with port 443' => ['uri' => 'https://[::1]:443/', 'port' => null],
        'scheme "http" and IP6 with port 444' => ['uri' => 'http://[::1]:444/', 'port' => 444],
        'port is "1"' => ['uri' => 'https://0:0@0:1/0?0#0', 'port' => 1],
    ]);

    \it('Parse port through constrictor with exception', function (string $uri) {
        new Uri($uri);
    })
        ->throws(InvalidArgumentException::class, 'Invalid URI')
        ->with([
            'scheme "https" with port 65536' => ['uri' => 'https://www.com:65536'],
            'scheme "http" with port 65536' => ['uri' => 'http://www.com:65536'],
            'scheme "http" with port -1' => ['uri' => 'http://www.com:-1'],
            'scheme "http" with port abc' => ['uri' => 'http://www.com:abc'],
            'scheme "https" with port abc' => ['uri' => 'https://www.com:abc'],
            'scheme "http" with IP4 port 66000' => ['uri' => 'http://192.168.0.1:66000'],
            'scheme "http" with IP6 port 66000' => ['uri' => 'http://[::1]:66000'],
        ])
    ;

    \it('Method withPort', function (Uri $uri, ?int $port, ?int $expectPort) {
        $new = $uri->withPort($port);

        \expect($new)->not->toBe($uri)
            ->and($new->getPort())->toBe($expectPort)
        ;
    })->with([
        'port is "1"' => [
            'uri' => new Uri('https://0:0@0:0/0?0#0'),
            'port' => 1,
            'expect' => 1,
        ],
        'scheme "http" port 80' => [
            'uri' => new Uri('http://www.com'),
            'port' => 80,
            'expectPort' => null,
        ],
        'scheme "http" port 0' => [
            'uri' => new Uri('http://www.com'),
            'port' => 0,
            'expectPort' => 0,
        ],
        'scheme "http" port null' => [
            'uri' => new Uri('http://www.com'),
            'port' => null,
            'expectPort' => null,
        ],
        'scheme "http" port 8080' => [
            'uri' => new Uri('http://www.com:8080'),
            'port' => 80,
            'expectPort' => null,
        ],
        'scheme "https" port 443' => [
            'uri' => new Uri('https://www.com:444'),
            'port' => 443,
            'expectPort' => null,
        ],
        'scheme "https" IP4 port 444' => [
            'uri' => new Uri('https://192.168.1.1'),
            'port' => 444,
            'expectPort' => 444,
        ],
        'scheme "http" IP6 port 80' => [
            'uri' => new Uri('http://[::1]:90'),
            'port' => 80,
            'expectPort' => null,
        ],
        'scheme "https" IP6 port 443' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 443,
            'expectPort' => null,
        ],
        'scheme "https" IP6 port 444' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 444,
            'expectPort' => 444,
        ],
        'scheme "https" IP6 port 0' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 0,
            'expectPort' => 0,
        ],
    ]);

    \it('Method withPort and exception', function (Uri $uri, int $port) {
        $uri->withPort($port);
    })
        ->throws(InvalidArgumentException::class, 'Invalid port')
        ->with([
            'scheme "http" port "-1"' => ['uri' => new Uri('http://www.com'), 'port' => -1],
            'scheme "https" port "65536"' => ['uri' => new Uri('http://www.com'), 'port' => 65536],
        ])
    ;
})
    ->covers(Uri::class)
;
