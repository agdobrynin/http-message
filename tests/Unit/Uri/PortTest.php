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
class PortTest extends TestCase
{
    public static function dataInitPort(): \Generator
    {
        yield 'empty URI' => ['uri' => '', 'port' => null];

        yield 'no scheme without port' => ['uri' => '//www.com/', 'port' => null];

        yield 'no scheme with port 80' => ['uri' => '//www.com:80/', 'port' => 80];

        yield 'no scheme with port 443' => ['uri' => '//www.com:443/', 'port' => 443];

        yield 'scheme "http" with port 80' => ['uri' => 'http://www.com:80/', 'port' => null];

        yield 'scheme "http" with port 8080' => ['uri' => 'http://www.com:8080/', 'port' => 8080];

        yield 'scheme "https" with port 443' => ['uri' => 'https://www.com:443', 'port' => null];

        yield 'scheme "https" with port 4444' => ['uri' => 'https://www.com:4444', 'port' => 4444];

        yield 'scheme "http" with port 0' => ['uri' => 'http://www.com:0', 'port' => 0];

        yield 'scheme "https" with port 0' => ['uri' => 'https://www.com:0', 'port' => 0];

        yield 'scheme "https" with port 65535' => ['uri' => 'https://www.com:65535', 'port' => 65535];

        yield 'scheme "http" and IP4 without port' => ['uri' => 'http://192.168.0.1/', 'port' => null];

        yield 'scheme "http" and IP4 with port 80' => ['uri' => 'http://192.168.0.1:80/', 'port' => null];

        yield 'scheme "http" and IP4 with port 81' => ['uri' => 'http://192.168.0.1:81/', 'port' => 81];

        yield 'scheme "https" and IP4 with port 443' => ['uri' => 'https://192.168.0.1:443/', 'port' => null];

        yield 'scheme "http" and IP4 with port 444' => ['uri' => 'http://192.168.0.1:444/', 'port' => 444];

        yield 'scheme "http" and IP6 without port' => ['uri' => 'http://[::1]/', 'port' => null];

        yield 'scheme "http" and IP6 with port 80' => ['uri' => 'http://[::1]:80/', 'port' => null];

        yield 'scheme "http" and IP6 with port 81' => ['uri' => 'http://[::1]:81/', 'port' => 81];

        yield 'scheme "https" and IP6 without port' => ['uri' => 'https://[::1]/', 'port' => null];

        yield 'scheme "https" and IP6 with port 443' => ['uri' => 'https://[::1]:443/', 'port' => null];

        yield 'scheme "http" and IP6 with port 444' => ['uri' => 'http://[::1]:444/', 'port' => 444];
    }

    /**
     * @dataProvider dataInitPort
     */
    public function testInitPort(string $uri, ?int $port): void
    {
        $this->assertEquals($port, (new Uri($uri))->getPort());
    }

    public static function dataInitPortInvalid(): \Generator
    {
        yield 'scheme "https" with port 65536' => ['uri' => 'https://www.com:65536'];

        yield 'scheme "http" with port 65536' => ['uri' => 'http://www.com:65536'];

        yield 'scheme "http" with port -1' => ['uri' => 'http://www.com:-1'];

        yield 'scheme "http" with port abc' => ['uri' => 'http://www.com:abc'];

        yield 'scheme "https" with port abc' => ['uri' => 'https://www.com:abc'];

        yield 'scheme "http" with IP4 port 66000' => ['uri' => 'http://192.168.0.1:66000'];

        yield 'scheme "http" with IP6 port 66000' => ['uri' => 'http://[::1]:66000'];
    }

    /**
     * @dataProvider dataInitPortInvalid
     */
    public function testInitPortInvalid(string $uri): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("[{$uri}]");

        new Uri($uri);
    }

    public static function dataWithPort(): \Generator
    {
        yield 'scheme "http" port 80' => [
            'uri' => new Uri('http://www.com'),
            'port' => 80,
            'expectPort' => null,
        ];

        yield 'scheme "http" port 0' => [
            'uri' => new Uri('http://www.com'),
            'port' => 0,
            'expectPort' => 0,
        ];

        yield 'scheme "http" port null' => [
            'uri' => new Uri('http://www.com'),
            'port' => null,
            'expectPort' => null,
        ];

        yield 'scheme "http" port 8080' => [
            'uri' => new Uri('http://www.com:8080'),
            'port' => 80,
            'expectPort' => null,
        ];

        yield 'scheme "https" port 443' => [
            'uri' => new Uri('https://www.com:444'),
            'port' => 443,
            'expectPort' => null,
        ];

        yield 'scheme "https" IP4 port 444' => [
            'uri' => new Uri('https://192.168.1.1'),
            'port' => 444,
            'expectPort' => 444,
        ];

        yield 'scheme "http" IP6 port 80' => [
            'uri' => new Uri('http://[::1]:90'),
            'port' => 80,
            'expectPort' => null,
        ];

        yield 'scheme "https" IP6 port 443' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 443,
            'expectPort' => null,
        ];

        yield 'scheme "https" IP6 port 444' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 444,
            'expectPort' => 444,
        ];

        yield 'scheme "https" IP6 port 0' => [
            'uri' => new Uri('https://[::1]'),
            'port' => 0,
            'expectPort' => 0,
        ];
    }

    /**
     * @dataProvider dataWithPort
     */
    public function testWithPort(Uri $uri, ?int $port, ?int $expectPort): void
    {
        $new = $uri->withPort($port);

        $this->assertNotSame($uri, $new);
        $this->assertEquals($expectPort, $new->getPort());
    }

    public static function dataWithPortException(): \Generator
    {
        yield 'scheme "http" port "-1"' => ['uri' => new Uri('http://www.com'), 'port' => -1];

        yield 'scheme "https" port "65536"' => ['uri' => new Uri('http://www.com'), 'port' => 65536];
    }

    /**
     * @dataProvider dataWithPortException
     */
    public function testWithPortException(Uri $uri, ?int $port): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid port [{$port}]");

        $uri->withPort($port);
    }
}
