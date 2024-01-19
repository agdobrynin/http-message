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
            'uri' => new Uri(''),
            'expect' => '',
        ],

        'has scheme and empty other' => [
            'uri' => (new Uri(''))->withScheme('https'),
            'expect' => 'https:',
        ],

        'has site only and standard port' => [
            'uri' => new Uri('https://mysite.com:443/'),
            'expect' => 'https://mysite.com/',
        ],

        'has scheme and authority' => [
            'uri' => (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withUserInfo('ivan ivanov', 'password+key'),
            'expect' => 'https://ivan%20ivanov:password+key@www.site.com',
        ],

        'path not absolute' => [
            'uri' => (new Uri(''))
                ->withHost('www.Site.COM')
                ->withPath('index.html'),
            'expect' => '//www.site.com/index.html',
        ],

        'path absolute' => [
            'uri' => (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withPath('/index.html'),
            'expect' => 'https://www.site.com/index.html',
        ],

        'path absolute. host and protocol not presented' => [
            'uri' => (new Uri(''))
                ->withPath('/////index.html'),
            'expect' => '/index.html',
        ],

        'query only ' => [
            'uri' => (new Uri(''))
                ->withQuery('abc=bbb&x=9f'),
            'expect' => '?abc=bbb&x=9f',
        ],

        'fragment only' => [
            'uri' => (new Uri(''))->withFragment('abc'),
            'expect' => '#abc',
        ],

        'query and fragment with unreserved symbols' => [
            'uri' => (new Uri(''))
                ->withScheme('HTTPS')
                ->withPort(444)
                ->withHost('SUPER-SITE.com')
                ->withPath('main.html')
                ->withFragment($unreservedForQueryAndFragment)
                ->withQuery($unreservedForQueryAndFragment),
            'expect' => 'https://super-site.com:444/main.html?'.$unreservedForQueryAndFragment.'#'.$unreservedForQueryAndFragment,
        ],

        'only path with unreserved symbols' => [
            'uri' => new Uri($unreservedForPath),
            'expect' => $unreservedForPath,
        ],

        'with many star slash in path' => [
            'uri' => new Uri('https://php.net:443//function///path'),
            'expect' => 'https://php.net//function///path',
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
