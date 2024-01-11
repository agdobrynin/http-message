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
class SchemeTest extends TestCase
{
    public static function dataInitScheme(): \Generator
    {
        yield 'empty URI' => ['uri' => '', 'scheme' => ''];

        yield 'string URI' => ['uri' => 'ww.site.com', 'scheme' => ''];

        yield 'scheme "https"' => ['uri' => 'HTTPS://MY.NET/', 'scheme' => 'https'];

        yield 'scheme "http"' => ['uri' => 'HttP://user@DOMAIN/', 'scheme' => 'http'];

        yield 'scheme "news"' => ['uri' => 'NEws://RELCOME.NET/', 'scheme' => 'news'];

        yield 'scheme "https" URI IP4 with port' => ['uri' => 'HTTPS://192.168.1.1:90/', 'scheme' => 'https'];

        yield 'scheme "https" URI IP6 with port' => ['uri' => 'HTTPS://[::1]:1025/', 'scheme' => 'https'];
    }

    /**
     * @dataProvider dataInitScheme
     */
    public function testInitHost(string $uri, string $scheme): void
    {
        $this->assertEquals($scheme, (new Uri($uri))->getScheme());
    }

    public static function dataWithScheme(): \Generator
    {
        yield 'Scheme empty change scheme https' => [
            'uri' => new Uri('//www.yahoo.com'),
            'scheme' => 'Https',
            'expect' => 'https',
        ];

        yield 'Scheme empty change scheme http' => [
            'uri' => new Uri('//www.yahoo.com'),
            'scheme' => 'HttP',
            'expect' => 'http',
        ];
    }

    /**
     * @dataProvider dataWithScheme
     */
    public function testWithHost(Uri $uri, string $scheme, string $expect): void
    {
        $new = $uri->withScheme($scheme);

        $this->assertNotSame($uri, $new);
        $this->assertEquals($expect, $new->getScheme());
    }
}
