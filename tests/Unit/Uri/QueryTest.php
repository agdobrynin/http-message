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
class QueryTest extends TestCase
{
    public static function dataQueryInConstructor(): \Generator
    {
        yield 'empty' => [
            'uri' => '',
            'query' => '',
        ];

        yield 'has host and query' => [
            'uri' => 'localhost/?p1=10&x[]=10',
            'query' => 'p1=10&x%5B%5D=10',
        ];

        yield 'no host but has query style string' => [
            'uri' => '?param=abc&host=10',
            'query' => 'param=abc&host=10',
        ];

        yield 'has uri without query string' => [
            'uri' => 'HTTPS://domain.com/index.html',
            'query' => '',
        ];
    }

    /**
     * @dataProvider dataQueryInConstructor
     */
    public function testQueryInConstructor(string $uri, string $query): void
    {
        $this->assertEquals($query, (new Uri($uri))->getQuery());
    }

    public static function dataWithQuery(): \Generator
    {
        yield 'empty' => [
            'uri' => new Uri(''),
            'query' => '',
            'expect' => '',
        ];

        yield 'exist uri and set empty query' => [
            'uri' => new Uri('https://www.com/document?abc=10&x[]=fix%20plan'),
            'query' => '',
            'expect' => '',
        ];

        yield 'uri and set query unavailable symbols' => [
            'uri' => new Uri('https://www.com/document'),
            // TODO how to fix square brace
            'query' => 'param money[]=€',
            'expect' => 'param%20money%5B%5D=%E2%82%AC',
        ];

        yield 'uri and set fragment available symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'query' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
            'expect' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
        ];
    }

    /**
     * @dataProvider dataWithQuery
     */
    public function testWithQuery(Uri $uri, string $query, string $expect): void
    {
        $new = $uri->withQuery($query);

        $this->assertNotSame($new, $uri);
        $this->assertEquals($expect, $new->getQuery());
    }
}
