<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Uri;

use Generator;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Uri::class)]
class ToStringTest extends TestCase
{
    #[DataProvider('dataProviderSuccess')]
    public function testSuccess(Uri $uri, string $expect): void
    {
        self::assertEquals($expect, (string) $uri);
    }

    public static function dataProviderSuccess(): Generator
    {
        $unreservedForQueryAndFragment = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?';
        $unreservedForPath = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/';

        yield 'empty' => [
            new Uri(''),
            '',
        ];

        yield 'many "0"' => [
            new Uri('https://0:0@0:1/0?0#0'),
            'https://0:0@0:1/0?0#0',
        ];

        yield 'has scheme and empty other' => [
            (new Uri(''))->withScheme('https'),
            'https:',
        ];

        yield 'has site only and standard port' => [
            new Uri('https://mysite.com:443/'),
            'https://mysite.com/',
        ];

        yield 'has scheme and authority' => [
            (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withUserInfo('ivan ivanov', 'password+key'),
            'https://ivan%20ivanov:password+key@www.site.com',
        ];

        yield 'path not absolute' => [
            (new Uri(''))
                ->withHost('www.Site.COM')
                ->withPath('index.html'),
            '//www.site.com/index.html',
        ];

        yield 'path absolute' => [
            (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withPath('/index.html'),
            'https://www.site.com/index.html',
        ];

        yield 'path absolute. host and protocol not presented' => [
            (new Uri(''))
                ->withPath('/////index.html'),
            '/index.html',
        ];

        yield 'query only ' => [
            (new Uri(''))
                ->withQuery('abc=bbb&x=9f'),
            '?abc=bbb&x=9f',
        ];

        yield 'fragment only' => [
            (new Uri(''))->withFragment('abc'),
            '#abc',
        ];

        yield 'query and fragment with unreserved symbols' => [
            (new Uri(''))
                ->withScheme('HTTPS')
                ->withPort(444)
                ->withHost('SUPER-SITE.com')
                ->withPath('main.html')
                ->withFragment($unreservedForQueryAndFragment)
                ->withQuery($unreservedForQueryAndFragment),
            'https://super-site.com:444/main.html?'.$unreservedForQueryAndFragment.'#'.$unreservedForQueryAndFragment,
        ];

        yield 'only path with unreserved symbols' => [
            new Uri($unreservedForPath),
            $unreservedForPath,
        ];

        yield 'with many star slash in path' => [
            new Uri('https://php.net:443//function///path'),
            'https://php.net//function///path',
        ];
    }

    public function testMakeFullPathWithSlashes(): void
    {
        $expected = 'http://php.org//make///path';
        $uri = new Uri($expected);

        $this->assertSame('/make///path', $uri->getPath());
        $this->assertSame($expected, (string) $uri);
    }
}
