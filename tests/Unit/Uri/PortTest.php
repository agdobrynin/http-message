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

        yield 'scheme "https" and IP4 without port' => ['uri' => 'https://192.168.0.1/', 'port' => null];

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
}
