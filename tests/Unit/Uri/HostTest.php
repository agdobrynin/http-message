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
class HostTest extends TestCase
{
    public static function dataInitHost(): \Generator
    {
        yield 'empty URI' => ['uri' => '', 'host' => ''];

        yield 'string URI' => ['uri' => 'ww.site.com', 'host' => ''];

        yield 'without scheme URI with big letter' => ['uri' => '//WWW.cOM/aaaa?anc=1', 'host' => 'www.com'];

        yield 'scheme "https" URI with big letter' => ['uri' => 'https://my.NET/?anc=1', 'host' => 'my.net'];

        yield 'scheme "http" URI host short' => ['uri' => 'http://DOMain/', 'host' => 'domain'];

        yield 'scheme "https" URI IP4 with port' => ['uri' => 'https://192.168.1.1:90/', 'host' => '192.168.1.1'];

        yield 'scheme "https" URI IP6 with port' => ['uri' => 'https://[::1]:1025/', 'host' => '[::1]'];
    }

    /**
     * @dataProvider dataInitHost
     */
    public function testInitHost(string $uri, string $host): void
    {
        $this->assertEquals($host, (new Uri($uri))->getHost());
    }

    public static function dataWithHost(): \Generator
    {
        yield 'Scheme empty change host' => [
            'uri' => new Uri('//www.yahoo.com'),
            'host' => 'www.NEWS.com',
            'expect' => 'www.news.com',
        ];

        yield 'Scheme empty change to IP4' => [
            'uri' => new Uri('//www.yahoo.com'),
            'host' => '8.8.8.8',
            'expect' => '8.8.8.8',
        ];

        yield 'Scheme empty change to IP6' => [
            'uri' => new Uri('//www.yahoo.com'),
            'host' => '[2001:0DB8:11A3:09D7:1F34:8A2E:07A0:765D]',
            'expect' => '[2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d]',
        ];

        yield 'Scheme "https" change to local domain' => [
            'uri' => new Uri('https://www.yahoo.com'),
            'host' => 'LOCALHOST',
            'expect' => 'localhost',
        ];

        yield 'Scheme "http" empty host' => [
            'uri' => new Uri('https://www.yahoo.com'),
            'host' => '',
            'expect' => '',
        ];

        yield 'Scheme "https" host internationally' => [
            'uri' => new Uri('https://xn--80aiifkqki.xn--p1ai/'),
            'host' => 'XN--H1AHN.XN--P1AI',
            'expect' => 'xn--h1ahn.xn--p1ai',
        ];
    }

    /**
     * @dataProvider dataWithHost
     */
    public function testWithHost(Uri $uri, string $host, string $expect): void
    {
        $new = $uri->withHost($host);

        $this->assertNotSame($uri, $new);
        $this->assertEquals($expect, $new->getHost());
    }
}
