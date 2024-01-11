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
class UserInfoTest extends TestCase
{
    public static function dataUserInfo(): \Generator
    {
        yield 'empty user in empty uri' => [
            'uri' => '',
            'expect' => '',
        ];

        yield 'empty user in uri' => [
            'uri' => '//www.com.net/',
            'expect' => '',
        ];

        yield 'set user with password in uri' => [
            'uri' => 'https://John:password@www.com.net/',
            'expect' => 'John:password',
        ];

        yield 'set user no with password in uri' => [
            'uri' => 'https://:password@www.com.net/',
            'expect' => '',
        ];

        yield 'set user without password in uri' => [
            'uri' => 'https://John@www.com.net/',
            'expect' => 'John',
        ];

        yield 'set user with password decoded  in uri' => [
            'uri' => 'http://%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203@localhost/',
            'expect' => '%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203',
        ];
    }

    /**
     * @dataProvider dataUserInfo
     */
    public function testUserInfoConstructor(string $uri, string $expect): void
    {
        $this->assertEquals($expect, (new Uri($uri))->getUserInfo());
    }

    public static function dataWithUserInfo(): \Generator
    {
        yield 'empty Init and set empty' => [
            'uri' => new Uri(''),
            'user' => '',
            'password' => null,
            'expect' => '',
        ];

        yield 'Init with user and password and set other user without password' => [
            'uri' => new Uri('//john%20doe:pass@www.com'),
            'user' => '~mag ray09._-',
            'password' => null,
            'expect' => '~mag%20ray09._-',
        ];

        yield 'Init with user and password and set other user with symbols' => [
            'uri' => new Uri('//john%20doe:pass@www.com'),
            'user' => '❤mag ray]',
            'password' => 'pass word',
            'expect' => '%E2%9D%A4mag%20ray%5D:pass%20word',
        ];

        yield 'Init with user and password and set other user with password plain chars' => [
            'uri' => new Uri('//john:pass@www.com'),
            'user' => 'mary',
            'password' => 'password',
            'expect' => 'mary:password',
        ];

        yield 'Init with user and password and set other user with raw url encode chars' => [
            'uri' => new Uri('//john:pass@www.com'),
            'user' => 'mary',
            'password' => '%5Bmag%20ray%5D:pass%20word@',
            'expect' => 'mary:%5Bmag%20ray%5D%3Apass%20word%40',
        ];

        yield 'Login and password contains only un encode symbols' => [
            'uri' => new Uri(''),
            'user' => 'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'password' => 'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'expect' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:a-zA-Z0-9_-.~!$&\'()*+,;=',
        ];
    }

    /**
     * @dataProvider dataWithUserInfo
     */
    public function testWithUserInfo(Uri $uri, string $user, ?string $password, string $expect): void
    {
        $newUri = $uri->withUserInfo($user, $password);

        $this->assertNotSame($newUri, $uri);
        $this->assertEquals($expect, $newUri->getUserInfo());
    }
}
