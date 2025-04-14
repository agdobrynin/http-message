<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;
use Psr\Http\Message\UriInterface;

\describe('Method __toString for '.Uri::class, function () {
    $unreservedForQueryAndFragment = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?';
    $unreservedForPath = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/';

    \it('test success', function (Uri $uri, string $expect) {
        \expect((string) $uri)->toBe($expect);
    })->with([
        'empty' => [
            new Uri(''),
            '',
        ],

        'many "0"' => [
            new Uri('https://0:0@0:1/0?0#0'),
            'https://0:0@0:1/0?0#0',
        ],

        'has scheme and empty other' => [
            (new Uri(''))->withScheme('https'),
            'https:',
        ],

        'has site only and standard port' => [
            new Uri('https://mysite.com:443/'),
            'https://mysite.com/',
        ],

        'has scheme and authority' => [
            (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withUserInfo('ivan ivanov', 'password+key'),
            'https://ivan%20ivanov:password+key@www.site.com',
        ],

        'path not absolute' => [
            (new Uri(''))
                ->withHost('www.Site.COM')
                ->withPath('index.html'),
            '//www.site.com/index.html',
        ],

        'path absolute' => [
            (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withPath('/index.html'),
            'https://www.site.com/index.html',
        ],

        'path absolute. host and protocol not presented' => [
            (new Uri(''))
                ->withPath('/////index.html'),
            '/index.html',
        ],

        'query only ' => [
            (new Uri(''))
                ->withQuery('abc=bbb&x=9f'),
            '?abc=bbb&x=9f',
        ],

        'fragment only' => [
            (new Uri(''))->withFragment('abc'),
            '#abc',
        ],

        'query and fragment with unreserved symbols' => [
            (new Uri(''))
                ->withScheme('HTTPS')
                ->withPort(444)
                ->withHost('SUPER-SITE.com')
                ->withPath('main.html')
                ->withFragment($unreservedForQueryAndFragment)
                ->withQuery($unreservedForQueryAndFragment),
            'https://super-site.com:444/main.html?'.$unreservedForQueryAndFragment.'#'.$unreservedForQueryAndFragment,
        ],

        'only path with unreserved symbols' => [
            new Uri($unreservedForPath),
            $unreservedForPath,
        ],

        'with many star slash in path' => [
            new Uri('https://php.net:443//function///path'),
            'https://php.net//function///path',
        ],
    ]);

    \it('make full path with slashes', function () {
        $expected = 'http://php.org//make///path';
        $uri = new Uri($expected);

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('/make///path', $uri->getPath());

        $this->assertSame($expected, (string) $uri);
    });
})
    ->covers(Uri::class)
;
