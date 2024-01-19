<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getUserInfo, withUserInfo for '.Uri::class, function () {
    \it('User info through constructor class', function (string $uri, string $expect) {
        \expect((new Uri($uri))->getUserInfo())->toBe($expect);
    })->with([
        'empty user in empty uri' => [
            'uri' => '',
            'expect' => '',
        ],
        'empty user in uri' => [
            'uri' => '//www.com.net/',
            'expect' => '',
        ],
        'set user with password in uri' => [
            'uri' => 'https://John:password@www.com.net/',
            'expect' => 'John:password',
        ],
        'set user no with password in uri' => [
            'uri' => 'https://:password@www.com.net/',
            'expect' => '',
        ],
        'set user without password in uri' => [
            'uri' => 'https://John@www.com.net/',
            'expect' => 'John',
        ],
        'set user with password decoded  in uri' => [
            'uri' => 'http://%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203@localhost/',
            'expect' => '%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203',
        ],
    ]);

    \it('Test method WithUserInfo', function (Uri $uri, string $user, ?string $password, string $expect) {
        $newUri = $uri->withUserInfo($user, $password);

        \expect($newUri->getUserInfo())->toBe($expect)
            ->and($newUri)->not->toBe($uri)
        ;
    })->with([
        'empty Init and set empty' => [
            'uri' => new Uri(''),
            'user' => '',
            'password' => null,
            'expect' => '',
        ],
        'Init with user and password and set other user without password' => [
            'uri' => new Uri('//john%20doe:pass@www.com'),
            'user' => '~mag ray09._-',
            'password' => null,
            'expect' => '~mag%20ray09._-',
        ],
        'Init with user and password and set other user with symbols' => [
            'uri' => new Uri('//john%20doe:pass@www.com'),
            'user' => '❤mag ray]',
            'password' => 'pass word',
            'expect' => '%E2%9D%A4mag%20ray%5D:pass%20word',
        ],
        'Init with user and password and set other user with password plain chars' => [
            'uri' => new Uri('//john:pass@www.com'),
            'user' => 'mary',
            'password' => 'password',
            'expect' => 'mary:password',
        ],
        'Init without user and password and set other user with password' => [
            'uri' => new Uri('//www.com'),
            'user' => 'mary@jain',
            'password' => 'password',
            'expect' => 'mary%40jain:password',
        ],
        'Init with user and password and set other user with raw url encode chars' => [
            'uri' => new Uri('//john:pass@www.com'),
            'user' => 'mary',
            'password' => '%5Bmag%20ray%5D:pass%20word@',
            'expect' => 'mary:%5Bmag%20ray%5D%3Apass%20word%40',
        ],
        'Login and password contains only un encode symbols' => [
            'uri' => new Uri(''),
            'user' => 'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'password' => 'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'expect' => 'a-zA-Z0-9_-.~!$&\'()*+,;=:a-zA-Z0-9_-.~!$&\'()*+,;=',
        ],
    ]);
})
    ->covers(Uri::class)
;
