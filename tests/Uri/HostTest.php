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
class HostTest extends TestCase
{
    #[DataProvider('dataProviderMethodGetHost')]
    public function testMethodGetHost(Uri $uri, string $host): void
    {
        self::assertEquals($host, $uri->getHost());
    }

    public static function dataProviderMethodGetHost(): Generator
    {
        yield 'empty URI' => [new Uri(''), ''];

        yield 'string URI without scheme' => [new Uri('ww.site.com'), ''];

        yield 'without scheme URI with big letter' => [new Uri('//WWW.cOM/aaaa?anc=1'), 'www.com'];

        yield 'scheme "https" URI with big letter' => [new Uri('https://my.NET/?anc=1'), 'my.net'];

        yield 'scheme "http" URI host short' => [new Uri('http://DOMain/'), 'domain'];

        yield 'scheme "https" URI IP4 with port' => [new Uri('https://192.168.1.1:90/'), '192.168.1.1'];

        yield 'scheme "https" URI IP6 with port' => [new Uri('https://[::1]:1025/'), '[::1]'];

        yield 'host is "0"' => [new Uri('https://0:0@0:1/0?0#0'), '0'];
    }

    #[DataProvider('dataProviderMethodWitHost')]
    public function testMethodWitHost(Uri $uri, string $host, string $expect): void
    {
        $new = $uri->withHost($host);

        self::assertNotSame($new, $host);
        self::assertEquals($expect, $new->getHost());
    }

    public static function dataProviderMethodWitHost(): Generator
    {
        yield 'Scheme empty change host' => [
            new Uri('//www.yahoo.com'),
            'www.NEWS.com',
            'www.news.com',
        ];

        yield 'Scheme empty change host to "0"' => [
            new Uri('//www.yahoo.com'),
            '0',
            '0',
        ];

        yield 'Scheme empty change to IP4' => [
            new Uri('//www.yahoo.com'),
            '8.8.8.8',
            '8.8.8.8',
        ];

        yield 'Scheme empty change to IP6' => [
            new Uri('//www.yahoo.com'),
            '[2001:0DB8:11A3:09D7:1F34:8A2E:07A0:765D]',
            '[2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d]',
        ];

        yield 'Scheme "https" change to local domain' => [
            new Uri('https://www.yahoo.com'),
            'LOCALHOST',
            'localhost',
        ];

        yield 'Scheme "http" empty host' => [
            new Uri('https://www.yahoo.com'),
            '',
            '',
        ];

        yield 'Scheme "https" host internationally' => [
            new Uri('https://xn--80aiifkqki.xn--p1ai/'),
            'XN--H1AHN.XN--P1AI',
            'xn--h1ahn.xn--p1ai',
        ];

        yield 'host cyrillic utf-8 - non ASCII symbols not convert to lowercase' => [
            new Uri(''),
            'Мир.РФ',
            'Мир.РФ',
        ];
    }
}
