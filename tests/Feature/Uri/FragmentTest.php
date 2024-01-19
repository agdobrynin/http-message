<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Methods getFragment, withFragment for '.Uri::class, function () {
    \it('Parse fragment through constructor', function (string $uri, string $fragment) {
        \expect((new Uri($uri))->getFragment())->toBe($fragment);
    })->with([
        'empty URI' => [
            'uri' => '',
            'fragment' => '',
        ],
        'has host and fragment only' => [
            'uri' => 'localhost/#frg-20',
            'fragment' => 'frg-20',
        ],
        'no host but has fragment style string' => [
            'uri' => 'index.html#frg-20',
            'fragment' => 'frg-20',
        ],
        'has uri without fragment' => [
            'uri' => 'HTTPS://domain.com/index.html',
            'fragment' => '',
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
            'uri' => new Uri(''),
            'fragment' => '',
            'expect' => '',
        ],

        'exist uri and set empty fragment' => [
            'uri' => new Uri('https://www.com/document#frag*'),
            'fragment' => '',
            'expect' => '',
        ],

        'uri and set fragment unavailable symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'fragment' => '€ [евро]',
            'expect' => '%E2%82%AC%20%5B%D0%B5%D0%B2%D1%80%D0%BE%5D',
        ],

        'uri and set fragment reserved symbols and one encode with PCT ENCODED' => [
            'uri' => new Uri('https://www.com/document'),
            'fragment' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
            'expect' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
        ],
    ]);
})
    ->covers(Uri::class)
;
