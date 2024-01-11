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
}
