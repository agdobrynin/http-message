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
    }

    /**
     * @dataProvider dataQueryInConstructor
     */
    public function testQueryInConstructor(string $uri, string $query): void
    {
        $this->assertEquals($query, (new Uri($uri))->getQuery());
    }
}
