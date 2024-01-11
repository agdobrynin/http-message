<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Unit\Uri;

use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Uri::class)]
class ToStringTest extends TestCase
{
    public static function dataToString(): \Generator
    {
        $unreservedForQueryAndFragment = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?';
        $unreservedForPath = 'a-zA-Z0-9_-.~!$&\'()*+,;=:@/';

        yield 'empty' => [
            'uri' => new Uri(''),
            'expect' => '',
        ];

        yield 'has scheme and empty other' => [
            'uri' => (new Uri(''))->withScheme('https'),
            'expect' => 'https:',
        ];

        yield 'has site only and standard port' => [
            'uri' => new Uri('https://mysite.com:443/'),
            'expect' => 'https://mysite.com/',
        ];

        yield 'has scheme and authority' => [
            'uri' => (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withUserInfo('ivan ivanov', 'password+key'),
            'expect' => 'https://ivan%20ivanov:password+key@www.site.com',
        ];

        yield 'path not absolute' => [
            'uri' => (new Uri(''))
                ->withHost('www.Site.COM')
                ->withPath('index.html'),
            'expect' => '//www.site.com/index.html',
        ];

        yield 'path absolute' => [
            'uri' => (new Uri(''))
                ->withScheme('https')
                ->withHost('www.Site.COM')
                ->withPath('/index.html'),
            'expect' => 'https://www.site.com/index.html',
        ];

        yield 'path absolute. host and protocol not presented' => [
            'uri' => (new Uri(''))
                ->withPath('/////index.html'),
            'expect' => '/index.html',
        ];

        yield 'query only ' => [
            'uri' => (new Uri(''))
                ->withQuery('abc=bbb&x=9f'),
            'expect' => '?abc=bbb&x=9f',
        ];

        yield 'fragment only' => [
            'uri' => (new Uri(''))->withFragment('abc'),
            'expect' => '#abc',
        ];

        yield 'query and fragment with unreserved symbols' => [
            'uri' => (new Uri(''))
                ->withScheme('HTTPS')
                ->withPort(444)
                ->withHost('SUPER-SITE.com')
                ->withPath('main.html')
                ->withFragment($unreservedForQueryAndFragment)
                ->withQuery($unreservedForQueryAndFragment),
            'expect' => 'https://super-site.com:444/main.html?'.$unreservedForQueryAndFragment.'#'.$unreservedForQueryAndFragment,
        ];

        yield 'only path with unreserved symbols' => [
            'uri' => new Uri($unreservedForPath),
            'expect' => $unreservedForPath,
        ];
    }

    /**
     * @dataProvider dataToString
     */
    public function testToString(Uri $uri, string $expect): void
    {
        $this->assertEquals((string) $uri, $expect);
    }
}
