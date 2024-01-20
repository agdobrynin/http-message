<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getAuthority for '.Uri::class, function () {
    \it('Authority method', function (string $uri, string $expect) {
        \expect((new Uri($uri))->getAuthority())->toBe($expect);
    })->with([
        'empty' => [
            'uri' => '',
            'expect' => '',
        ],
        'host only' => [
            'uri' => '//site',
            'expect' => 'site',
        ],
        'host with user "0" and pass "0"' => [
            'uri' => 'https://0:0@0:1/0?0#0',
            'expect' => '0:0@0:1',
        ],
        'host only and standard port' => [
            'uri' => 'https://site:443',
            'expect' => 'site',
        ],
        'host only and non standard port' => [
            'uri' => 'https://site:445',
            'expect' => 'site:445',
        ],
        'with user only and host and non standard port' => [
            'uri' => 'https://pi%20ter@site:445/index.html',
            'expect' => 'pi%20ter@site:445',
        ],
        'with user and password' => [
            'uri' => 'https://root:pass@site/dir',
            'expect' => 'root:pass@site',
        ],
        'with host IP4 and password with encoded symbols' => [
            'uri' => 'http://mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1:80/site/',
            'expect' => 'mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1',
        ],
        'with host IP6' => [
            'uri' => 'https://mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]:443/index.php',
            'expect' => 'mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]',
        ],
    ]);

    \it('Authority without host', function () {
        $uri = (new Uri(''))->withUserInfo('root', 'password');

        \expect($uri->getUserInfo())->toBe('root:password')
            ->and($uri->getAuthority())->toBeEmpty()
        ;
    });
})
    ->covers(Uri::class)
;
