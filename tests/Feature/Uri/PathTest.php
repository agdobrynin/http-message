<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPath, withPath for '.Uri::class, function () {
    \it('Parse path component through constructor', function (string $uri, string $path) {
        \expect((new Uri($uri))->getPath())
            ->toBe($path)
        ;
    })->with([
        'empty' => ['', ''],

        'set #1' => ['https://user:pass@example.com:8080/path/123?q=abc#test', '/path/123'],
        'set #2' => ['//example.org?q#h', ''],
        'set #3' => ['//example/a:x', '/a:x'],
        'set #4' => ['//example/../../etc/passwd', '/../../etc/passwd'],
        'set #5' => ['//example//etc//passwd/', '/etc//passwd/'],
        'set #6' => ['http://example.org//valid///path', '/valid///path'],
        'set #7' => ['https://0:0@0:1/0?0#0', '/0'],
    ]);

    \it('Method withPath', function (Uri $uri, string $path, string $expect) {
        $new = $uri->withPath($path);

        \expect($new)->not->toBe($uri)
            ->and($new->getPath())->toBe($expect)
        ;
    })->with([
        'empty path' => [
            new Uri(''), '', '',
        ],

        'with unavailable symbols - "urlencode" use' => [
            new Uri('http://www.com/index.html'),
            'dir/просто.html',
            '/dir/%D0%BF%D1%80%D0%BE%D1%81%D1%82%D0%BE.html',
        ],

        'with reserved symbols only' => [
            new Uri('http://www.com'),
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
            '/a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
        ],
        'path is "0"' => [
            new Uri('https://0:0@0:1/0?0#0'),
            '0',
            '/0',
        ],
    ]);
})
    ->covers(Uri::class)
;
