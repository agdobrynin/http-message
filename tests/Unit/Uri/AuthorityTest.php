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
class AuthorityTest extends TestCase
{
    public static function dataAuthority(): \Generator
    {
        yield 'empty' => [
            'uri' => '',
            'expect' => '',
        ];

        yield 'host only' => [
            'uri' => '//site',
            'expect' => 'site',
        ];

        yield 'host only and standard port' => [
            'uri' => 'https://site:443',
            'expect' => 'site',
        ];

        yield 'host only and non standard port' => [
            'uri' => 'https://site:445',
            'expect' => 'site:445',
        ];

        yield 'with user only and host and non standard port' => [
            'uri' => 'https://pi%20ter@site:445/index.html',
            'expect' => 'pi%20ter@site:445',
        ];

        yield 'with user and password' => [
            'uri' => 'https://root:pass@site/dir',
            'expect' => 'root:pass@site',
        ];

        yield 'with host IP4 and password with encoded symbols' => [
            'uri' => 'http://mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1:80/site/',
            'expect' => 'mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1',
        ];

        yield 'with host IP6' => [
            'uri' => 'https://mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]:443/index.php',
            'expect' => 'mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]',
        ];
    }

    /**
     * @dataProvider dataAuthority
     */
    public function testAuthority(string $uri, string $expect): void
    {
        $uri = new Uri($uri);

        $this->assertEquals($expect, $uri->getAuthority());
    }

    public function testAuthorityWithoutHost(): void
    {
        $uri = (new Uri(''))->withUserInfo('root', 'password');

        $this->assertEquals('root:password', $uri->getUserInfo());
        $this->assertEquals('', $uri->getAuthority());
    }
}
