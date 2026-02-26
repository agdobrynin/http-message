<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Uri;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Uri::class)]
class SchemeTest extends TestCase
{
    #[DataProvider('dataProvideParseSchemeThroughConstructor')]
    public function testParseSchemeThroughConstructor(string $uri, string $scheme): void
    {
        self::assertEquals($scheme, (new Uri($uri))->getScheme());
    }

    public static function dataProvideParseSchemeThroughConstructor(): Generator
    {
        yield 'empty URI' => ['', ''];

        yield 'string URI' => ['ww.site.com', ''];

        yield 'scheme "https"' => ['HTTPS://MY.NET/', 'https'];

        yield 'scheme "http"' => ['HttP://user@DOMAIN/', 'http'];

        yield 'scheme "news"' => ['NEws://RELCOME.NET/', 'news'];

        yield 'scheme "https" URI IP4 with port' => ['HTTPS://192.168.1.1:90/', 'https'];

        yield 'scheme "https" URI IP6 with port' => ['HTTPS://[::1]:1025/', 'https'];
    }

    #[DataProvider('dataMethodWithScheme')]
    public function testMethodWithScheme(Uri $uri, string $scheme, string $expect): void
    {
        $new = $uri->withScheme($scheme);

        self::assertNotSame($uri, $new);
        self::assertEquals($expect, $new->getScheme());
    }

    public static function dataMethodWithScheme(): Generator
    {
        yield 'Scheme empty change scheme https' => [
            new Uri('//www.yahoo.com'),
            'Https',
            'https',
        ];

        yield 'Scheme empty change scheme http' => [
            new Uri('//www.yahoo.com'),
            'HttP',
            'http',
        ];

        yield 'Scheme http change to empty' => [
            new Uri('http://www.yahoo.com'),
            '',
            '',
        ];
    }

    #[DataProvider('dataProviderSchemeMustBeAString')]
    public function testSchemeMustBeAString(mixed $scheme): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scheme must be a string');

        (new Uri(''))->withScheme($scheme);
    }

    public static function dataProviderSchemeMustBeAString(): Generator
    {
        yield [true];

        yield [false];

        yield [70];

        yield [[]];

        yield [(object) []];

        yield [new stdClass()];

        yield [new Uri('')];
    }
}
