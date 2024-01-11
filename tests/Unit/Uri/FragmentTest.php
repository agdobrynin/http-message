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
class FragmentTest extends TestCase
{
    public static function dataFragmentInConstructor(): \Generator
    {
        yield 'empty' => [
            'uri' => '',
            'fragment' => '',
        ];

        yield 'has host and fragment' => [
            'uri' => 'localhost/#frg-20',
            'fragment' => 'frg-20',
        ];

        yield 'no host but has fragment style string' => [
            'uri' => 'index.html#frg-20',
            'fragment' => 'frg-20',
        ];

        yield 'has uri without fragment' => [
            'uri' => 'HTTPS://domain.com/index.html',
            'fragment' => '',
        ];
    }

    /**
     * @dataProvider dataFragmentInConstructor
     */
    public function testFragmentInConstructor(string $uri, string $fragment): void
    {
        $this->assertEquals($fragment, (new Uri($uri))->getFragment());
    }

    public static function dataWithFragment(): \Generator
    {
        yield 'empty' => [
            'uri' => new Uri(''),
            'fragment' => '',
            'expect' => '',
        ];

        yield 'exist uri and set empty fragment' => [
            'uri' => new Uri('https://www.com/document#frag*'),
            'fragment' => '',
            'expect' => '',
        ];

        yield 'uri and set fragment unavailable symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'fragment' => '€ [евро]',
            'expect' => '%E2%82%AC%20%5B%D0%B5%D0%B2%D1%80%D0%BE%5D',
        ];

        yield 'uri and set fragment available symbols' => [
            'uri' => new Uri('https://www.com/document'),
            'fragment' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
            'expect' => 'azAF0~7.!-$_&\'(*)+,;=:@?/%E2%82%AC',
        ];
    }

    /**
     * @dataProvider dataWithFragment
     */
    public function testWithFragment(Uri $uri, string $fragment, string $expect): void
    {
        $new = $uri->withFragment($fragment);

        $this->assertNotSame($new, $uri);
        $this->assertEquals($expect, $new->getFragment());
    }
}
