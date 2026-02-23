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
class UserInfoTest extends TestCase
{
    #[DataProvider('dataProviderUserInfoThroughConstructor')]
    public function testUserInfoThroughConstructor(string $uri, string $expect): void
    {
        self::assertEquals($expect, (new Uri($uri))->getUserInfo());
    }

    public static function dataProviderUserInfoThroughConstructor(): Generator
    {
        yield 'empty user in empty uri' => [
            '',
            '',
        ];

        yield 'empty user in uri' => [
            '//www.com.net/',
            '',
        ];

        yield 'set user with password in uri' => [
            'https://John:password@www.com.net/',
            'John:password',
        ];

        yield 'set user no with password in uri' => [
            'https://:password@www.com.net/',
            '',
        ];

        yield 'set user without password in uri' => [
            'https://John@www.com.net/',
            'John',
        ];

        yield 'set user with password decoded  in uri' => [
            'http://%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203@localhost/',
            '%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203',
        ];

        yield 'user "0" and password "0"' => [
            'https://0:0@0:1/0?0#0',
            '0:0',
        ];
    }

    #[DataProvider('dataProviderMethodWithUserInfo')]
    public function testMethodWithUserInfo(Uri $uri, string $user, ?string $password, string $expect): void
    {
        $newUri = $uri->withUserInfo($user, $password);

        self::assertNotSame($uri, $newUri);
        self::assertEquals($expect, $newUri->getUserInfo());
    }

    public static function dataProviderMethodWithUserInfo(): Generator
    {
        yield 'empty Init and set empty' => [
            new Uri(''),
            '',
            null,
            '',
        ];

        yield 'Init with user and password and set other user without password' => [
            new Uri('//john%20doe:pass@www.com'),
            '~mag ray09._-',
            null,
            '~mag%20ray09._-',
        ];

        yield 'Init with user and password and set other user with symbols' => [
            new Uri('//john%20doe:pass@www.com'),
            '❤mag ray]',
            'pass word',
            '%E2%9D%A4mag%20ray%5D:pass%20word',
        ];

        yield 'Init with user and password and set other user with password plain chars' => [
            new Uri('//john:pass@www.com'),
            'mary',
            'password',
            'mary:password',
        ];

        yield 'Init without user and password and set other user with password' => [
            new Uri('//www.com'),
            'mary@jain',
            'password',
            'mary%40jain:password',
        ];

        yield 'Init with user and password and set other user with raw url encode chars' => [
            new Uri('//john:pass@www.com'),
            'mary',
            '%5Bmag%20ray%5D:pass%20word@',
            'mary:%5Bmag%20ray%5D%3Apass%20word%40',
        ];

        yield 'Login and password contains only un encode symbols' => [
            new Uri(''),
            'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:a-zA-Z0-9_-.~!$&\'()*+,;=',
        ];

        yield 'user "0" and password "0"' => [
            new Uri(''),
            '0',
            '0',
            '0:0',
        ];
    }
}
