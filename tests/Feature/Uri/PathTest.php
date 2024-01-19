<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getPath, withPath for '.Uri::class, function () {
    \it('Parse path component through constructor', function (string $uri, string $path) {
        \expect((new Uri($uri))->getPath())
            ->toBe($path)
        ;
    })->with([
        'empty' => ['uri' => '', 'path' => ''],

        'set #1' => ['uri' => 'https://user:pass@example.com:8080/path/123?q=abc#test', 'path' => '/path/123'],
        'set #2' => ['uri' => '//example.org?q#h', 'path' => ''],
        'set #3' => ['uri' => '//example/a:x', 'path' => '/a:x'],
        'set #4' => ['uri' => '//example/../../etc/passwd', 'path' => '/../../etc/passwd'],
        'set #5' => ['uri' => '//example//etc//passwd/', 'path' => '/etc//passwd/'],
        'set #6' => ['uri' => 'http://example.org//valid///path', 'path' => '/valid///path'],
    ]);

    \it('Method withPath', function (Uri $uri, string $path, string $expect) {
        $new = $uri->withPath($path);

        \expect($new)->not->toBe($uri)
            ->and($new->getPath())->toBe($expect)
        ;
    })->with([
        'empty path' => [
            'uri' => new Uri(''), 'path' => '', 'expect' => '',
        ],

        'with unavailable symbols - "urlencode" use' => [
            'uri' => new Uri('http://www.com/index.html'),
            'path' => 'dir/просто.html',
            'expect' => '/dir/%D0%BF%D1%80%D0%BE%D1%81%D1%82%D0%BE.html',
        ],

        'with reserved symbols only' => [
            'uri' => new Uri('http://www.com'),
            'path' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
            'expect' => '/a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
        ],
    ]);
})
    ->covers(Uri::class)
;
