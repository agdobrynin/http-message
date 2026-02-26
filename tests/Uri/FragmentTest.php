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
class FragmentTest extends TestCase
{
    #[DataProvider('dataProviderParseFragmentThroughConstructor')]
    public function testParseFragmentThroughConstructor(string $uri, string $fragment): void
    {
        self::assertEquals($fragment, (new Uri($uri))->getFragment());
    }

    public static function dataProviderParseFragmentThroughConstructor(): Generator
    {
        yield 'empty URI' => [
            '',
            '',
        ];

        yield 'has host and fragment only' => [
            'localhost/#frg-20',
            'frg-20',
        ];

        yield 'no host but has fragment style string' => [
            'index.html#frg-20',
            'frg-20',
        ];

        yield 'has uri without fragment' => [
            'HTTPS://domain.com/index.html',
            '',
        ];

        yield 'fragment is "0"' => [
            'https://0:0@0:1/0?0#0',
            '0',
        ];
    }

    #[DataProvider('dataProviderMethodWithFragment')]
    public function testMethodWithFragment(Uri $uri, string $fragment, string $expect): void
    {
        $new = $uri->withFragment($fragment);

        self::assertNotSame($uri, $new);
        self::assertEquals($expect, $new->getFragment());
    }

    public static function dataProviderMethodWithFragment(): Generator
    {
        yield 'fragment empty' => [
            new Uri(''),
            '',
            '',
        ];

        yield 'exist uri and set empty fragment' => [
            new Uri('https://www.com/document#frag*'),
            '',
            '',
        ];

        yield 'uri and set fragment unavailable symbols' => [
            new Uri('https://www.com/document'),
            '€ [евро]',
            '%E2%82%AC%20%5B%D0%B5%D0%B2%D1%80%D0%BE%5D',
        ];

        yield 'uri and set fragment reserved symbols and one encode with PCT ENCODED' => [
            new Uri('https://www.com/document'),
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/?%E2%82%AC',
        ];

        yield 'fragment is "0"' => [
            new Uri('https://0:0@0:1/0?0#fig2'),
            '0',
            '0',
        ];
    }
}
