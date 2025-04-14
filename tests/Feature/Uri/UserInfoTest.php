<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getUserInfo, withUserInfo for '.Uri::class, function () {
    \it('User info through constructor class', function (string $uri, string $expect) {
        \expect((new Uri($uri))->getUserInfo())->toBe($expect);
    })->with([
        'empty user in empty uri' => [
            '',
            '',
        ],
        'empty user in uri' => [
            '//www.com.net/',
            '',
        ],
        'set user with password in uri' => [
            'https://John:password@www.com.net/',
            'John:password',
        ],
        'set user no with password in uri' => [
            'https://:password@www.com.net/',
            '',
        ],
        'set user without password in uri' => [
            'https://John@www.com.net/',
            'John',
        ],
        'set user with password decoded  in uri' => [
            'http://%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203@localhost/',
            '%D1%8E%D0%B7%D0%B5%D1%80%201:%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C%203',
        ],
        'user "0" and password "0"' => [
            'https://0:0@0:1/0?0#0',
            '0:0',
        ],
    ]);

    \it('Test method WithUserInfo', function (Uri $uri, string $user, ?string $password, string $expect) {
        $newUri = $uri->withUserInfo($user, $password);

        \expect($newUri->getUserInfo())->toBe($expect)
            ->and($newUri)->not->toBe($uri)
        ;
    })->with([
        'empty Init and set empty' => [
            new Uri(''),
            '',
            null,
            '',
        ],
        'Init with user and password and set other user without password' => [
            new Uri('//john%20doe:pass@www.com'),
            '~mag ray09._-',
            null,
            '~mag%20ray09._-',
        ],
        'Init with user and password and set other user with symbols' => [
            new Uri('//john%20doe:pass@www.com'),
            '❤mag ray]',
            'pass word',
            '%E2%9D%A4mag%20ray%5D:pass%20word',
        ],
        'Init with user and password and set other user with password plain chars' => [
            new Uri('//john:pass@www.com'),
            'mary',
            'password',
            'mary:password',
        ],
        'Init without user and password and set other user with password' => [
            new Uri('//www.com'),
            'mary@jain',
            'password',
            'mary%40jain:password',
        ],
        'Init with user and password and set other user with raw url encode chars' => [
            new Uri('//john:pass@www.com'),
            'mary',
            '%5Bmag%20ray%5D:pass%20word@',
            'mary:%5Bmag%20ray%5D%3Apass%20word%40',
        ],
        'Login and password contains only un encode symbols' => [
            new Uri(''),
            'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'a-zA-Z0-9_-.~!$&\'()*+,;=',
            'a-zA-Z0-9_-.~!$&\'()*+,;=:a-zA-Z0-9_-.~!$&\'()*+,;=',
        ],
        'user "0" and password "0"' => [
            new Uri(''),
            '0',
            '0',
            '0:0',
        ],
    ]);
})
    ->covers(Uri::class)
;
