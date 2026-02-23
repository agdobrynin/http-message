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
class PathTest extends TestCase
{
    #[DataProvider('dataProviderParsePathComponentThroughConstructor')]
    public function testParsePathComponentThroughConstructor(string $uri, string $path): void
    {
        self::assertEquals($path, (new Uri($uri))->getPath());
    }

    public static function dataProviderParsePathComponentThroughConstructor(): Generator
    {
        yield 'empty' => ['', ''];

        yield 'set #1' => ['https://user:pass@example.com:8080/path/123?q=abc#test', '/path/123'];

        yield 'set #2' => ['//example.org?q#h', ''];

        yield 'set #3' => ['//example/a:x', '/a:x'];

        yield 'set #4' => ['//example/../../etc/passwd', '/../../etc/passwd'];

        yield 'set #5' => ['//example//etc//passwd/', '/etc//passwd/'];

        yield 'set #6' => ['http://example.org//valid///path', '/valid///path'];

        yield 'set #7' => ['https://0:0@0:1/0?0#0', '/0'];
    }

    #[DataProvider('dataProviderMethodWithPath')]
    public function testMethodWithPath(Uri $uri, string $path, string $expect): void
    {
        $new = $uri->withPath($path);

        self::assertNotSame($uri, $new);
        self::assertEquals($expect, $new->getPath());
    }

    public static function dataProviderMethodWithPath(): Generator
    {
        yield 'empty path' => [
            new Uri(''),
            '',
            '',
        ];

        yield 'with unavailable symbols - "urlencode" use' => [
            new Uri('http://www.com/index.html'),
            'dir/просто.html',
            '/dir/%D0%BF%D1%80%D0%BE%D1%81%D1%82%D0%BE.html',
        ];

        yield 'with reserved symbols only' => [
            new Uri('http://www.com'),
            'a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
            '/a-zA-Z0-9_-.~!$&\'()*+,;=:@/%d0%bf',
        ];

        yield 'path is "0"' => [
            new Uri('https://0:0@0:1/0?0#0'),
            '0',
            '/0',
        ];
    }
}
