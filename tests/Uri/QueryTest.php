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
class QueryTest extends TestCase
{
    #[DataProvider('dataProviderParseQueryStringThroughConstructor')]
    public function testParseQueryStringThroughConstructor(string $uri, string $query): void
    {
        self::assertEquals($query, (new Uri($uri))->getQuery());
    }

    public static function dataProviderParseQueryStringThroughConstructor(): Generator
    {
        yield 'empty' => [
            '',
            '',
        ];

        yield 'has host and query' => [
            'localhost/?p1=10&x[]=10',
            'p1=10&x%5B%5D=10',
        ];

        yield 'no host but has query style string' => [
            '?param=abc&host=10',
            'param=abc&host=10',
        ];

        yield 'with reserved symbols only' => [
            'http://www.com/?a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?',
        ];

        yield 'has uri without query string' => [
            'HTTPS://domain.com/index.html',
            '',
        ];

        yield 'query is "0"' => [
            'https://0:0@0:1/0?0#0',
            '0',
        ];
    }

    #[DataProvider('dataProviderMethodWithQuery')]
    public function testMethodWithQuery(Uri $uri, string $query, string $expect): void
    {
        $new = $uri->withQuery($query);

        self::assertNotSame($uri, $new);
        self::assertEquals($expect, $new->getQuery());
    }

    public static function dataProviderMethodWithQuery(): Generator
    {
        yield 'empty' => [
            new Uri(''),
            '',
            '',
        ];

        yield 'exist uri and set empty query' => [
            new Uri('https://www.com/document?abc=10&x[]=fix%20plan'),
            '',
            '',
        ];

        yield 'uri and set query unavailable symbols' => [
            new Uri('https://www.com/document'),
            'param money[]=€',
            'param%20money%5B%5D=%E2%82%AC',
        ];

        yield 'uri and set fragment available symbols' => [
            new Uri('https://www.com/document'),
            'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
            'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
        ];

        yield 'query is "0"' => [
            new Uri(''),
            '0',
            '0',
        ];
    }
}
