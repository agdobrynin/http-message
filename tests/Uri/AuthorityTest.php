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
class AuthorityTest extends TestCase
{
    #[DataProvider('uriProvider')]
    public function testAuthorityMethod(string $uri, string $expect): void
    {
        self::assertEquals($expect, (new Uri($uri))->getAuthority());
    }

    public static function uriProvider(): Generator
    {
        yield 'empty' => ['', ''];

        yield 'host only' => ['//site', 'site'];

        yield 'host with user "0" and pass "0"' => ['https://0:0@0:1/0?0#0', '0:0@0:1'];

        yield 'host only and standard port' => ['https://site:443', 'site'];

        yield 'host only and non standard port' => ['https://site:445', 'site:445'];

        yield 'with user only and host and non standard port' => [
            'https://pi%20ter@site:445/index.html',
            'pi%20ter@site:445',
        ];

        yield 'with user and password' => ['https://root:pass@site/dir', 'root:pass@site'];

        yield 'with host IP4 and password with encoded symbols' => [
            'http://mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1:80/site/',
            'mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1',
        ];

        yield 'with host IP6' => [
            'https://mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]:443/index.php',
            'mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]',
        ];
    }

    public function testAuthorityWithoutHost(): void
    {
        $uri = (new Uri(''))->withUserInfo('root', 'password');

        self::assertEquals('root:password', $uri->getUserInfo());
        self::assertEmpty($uri->getAuthority());
    }
}
