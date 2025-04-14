<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Methods getFragment, withFragment for '.Uri::class, function () {
    \it('Parse fragment through constructor', function (string $uri, string $fragment) {
        \expect((new Uri($uri))->getFragment())->toBe($fragment);
    })->with([
        'empty URI' => [
            '',
            '',
        ],
        'has host and fragment only' => [
            'localhost/#frg-20',
            'frg-20',
        ],
        'no host but has fragment style string' => [
            'index.html#frg-20',
            'frg-20',
        ],
        'has uri without fragment' => [
            'HTTPS://domain.com/index.html',
            '',
        ],
        'fragment is "0"' => [
            'https://0:0@0:1/0?0#0',
            '0',
        ],
    ]);

    \it('Method withFragment', function (Uri $uri, string $fragment, string $expect) {
        $new = $uri->withFragment($fragment);

        \expect($new)
            ->not->toBe($uri)
        ;

        \expect($new->getFragment())->toBe($expect);
    })->with([
        'fragment empty' => [
            new Uri(''),
            '',
            '',
        ],

        'exist uri and set empty fragment' => [
            new Uri('https://www.com/document#frag*'),
            '',
            '',
        ],

        'uri and set fragment unavailable symbols' => [
            new Uri('https://www.com/document'),
            '€ [евро]',
            '%E2%82%AC%20%5B%D0%B5%D0%B2%D1%80%D0%BE%5D',
        ],

        'uri and set fragment reserved symbols and one encode with PCT ENCODED' => [
            new Uri('https://www.com/document'),
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
        ],
        'fragment is "0"' => [
            new Uri('https://0:0@0:1/0?0#fig2'),
            '0',
            '0',
        ],
    ]);
})
    ->covers(Uri::class)
;
