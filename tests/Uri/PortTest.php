<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Uri;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Uri::class)]
class PortTest extends TestCase
{
    #[DataProvider('dataProviderParsePortThroughConstructor')]
    public function testParsePortThroughConstructor(string $uri, ?int $port): void
    {
        self::assertEquals($port, (new Uri($uri))->getPort());
    }

    public static function dataProviderParsePortThroughConstructor(): Generator
    {
        yield 'empty URI' => ['', null];

        yield 'no scheme without port' => ['//www.com/', null];

        yield 'no scheme with port 80' => ['//www.com:80/', 80];

        yield 'no scheme with port 443' => ['//www.com:443/', 443];

        yield 'scheme "http" with port 80' => ['http://www.com:80/', null];

        yield 'scheme "http" with port 8080' => ['http://www.com:8080/', 8080];

        yield 'scheme "https" with port 443' => ['https://www.com:443', null];

        yield 'scheme "https" with port 4444' => ['https://www.com:4444', 4444];

        yield 'scheme "http" with port 0' => ['http://www.com:0', 0];

        yield 'scheme "https" with port 0' => ['https://www.com:0', 0];

        yield 'scheme "https" with port 65535' => ['https://www.com:65535', 65535];

        yield 'scheme "http" and IP4 without port' => ['http://192.168.0.1/', null];

        yield 'scheme "http" and IP4 with port 80' => ['http://192.168.0.1:80/', null];

        yield 'scheme "http" and IP4 with port 81' => ['http://192.168.0.1:81/', 81];

        yield 'scheme "https" and IP4 with port 443' => ['https://192.168.0.1:443/', null];

        yield 'scheme "http" and IP4 with port 444' => ['http://192.168.0.1:444/', 444];

        yield 'scheme "http" and IP6 without port' => ['http://[::1]/', null];

        yield 'scheme "http" and IP6 with port 80' => ['http://[::1]:80/', null];

        yield 'scheme "http" and IP6 with port 81' => ['http://[::1]:81/', 81];

        yield 'scheme "https" and IP6 without port' => ['https://[::1]/', null];

        yield 'scheme "https" and IP6 with port 443' => ['https://[::1]:443/', null];

        yield 'scheme "http" and IP6 with port 444' => ['http://[::1]:444/', 444];

        yield 'port is "1"' => ['https://0:0@0:1/0?0#0', 1];
    }

    #[DataProvider('dataProviderParsePortThroughConstrictorWithException')]
    public function testParsePortThroughConstrictorWithException(string $uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI');

        new Uri($uri);
    }

    public static function dataProviderParsePortThroughConstrictorWithException(): Generator
    {
        yield 'scheme "https" with port 65536' => ['https://www.com:65536'];

        yield 'scheme "http" with port 65536' => ['http://www.com:65536'];

        yield 'scheme "http" with port -1' => ['http://www.com:-1'];

        yield 'scheme "http" with port abc' => ['http://www.com:abc'];

        yield 'scheme "https" with port abc' => ['https://www.com:abc'];

        yield 'scheme "http" with IP4 port 66000' => ['http://192.168.0.1:66000'];

        yield 'scheme "http" with IP6 port 66000' => ['http://[::1]:66000'];
    }

    #[DataProvider('dataProviderMethodWithPort')]
    public function testMethodWithPort(Uri $uri, ?int $port, ?int $expectPort): void
    {
        $new = $uri->withPort($port);

        self::assertNotSame($uri, $new);
        self::assertEquals($expectPort, $new->getPort());
    }

    public static function dataProviderMethodWithPort(): Generator
    {
        yield 'port is "1"' => [
            new Uri('https://0:0@0:0/0?0#0'),
            1,
            1,
        ];

        yield 'scheme "http" port 80' => [
            new Uri('http://www.com'),
            80,
            null,
        ];

        yield 'scheme "http" port 0' => [
            new Uri('http://www.com'),
            0,
            0,
        ];

        yield 'scheme "http" port null' => [
            new Uri('http://www.com'),
            null,
            null,
        ];

        yield 'scheme "http" port 8080' => [
            new Uri('http://www.com:8080'),
            80,
            null,
        ];

        yield 'scheme "https" port 443' => [
            new Uri('https://www.com:444'),
            443,
            null,
        ];

        yield 'scheme "https" IP4 port 444' => [
            new Uri('https://192.168.1.1'),
            444,
            444,
        ];

        yield 'scheme "http" IP6 port 80' => [
            new Uri('http://[::1]:90'),
            80,
            null,
        ];

        yield 'scheme "https" IP6 port 443' => [
            new Uri('https://[::1]'),
            443,
            null,
        ];

        yield 'scheme "https" IP6 port 444' => [
            new Uri('https://[::1]'),
            444,
            444,
        ];

        yield 'scheme "https" IP6 port 0' => [
            new Uri('https://[::1]'),
            0,
            0,
        ];
    }

    #[DataProvider('dataProviderMethodWithPortAndException')]
    public function testMethodWithPortAndException(Uri $uri, int $port): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port');

        $uri->withPort($port);
    }

    public static function dataProviderMethodWithPortAndException(): Generator
    {
        yield 'scheme "http" port "-1"' => [new Uri('http://www.com'), -1];

        yield 'scheme "https" port "65536"' => [new Uri('http://www.com'), 65536];
    }
}
