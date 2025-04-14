<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\describe('Method getAuthority for '.Uri::class, function () {
    \it('Authority method', function (string $uri, string $expect) {
        \expect((new Uri($uri))->getAuthority())->toBe($expect);
    })->with([
        'empty' => [
            '',
            '',
        ],
        'host only' => [
            '//site',
            'site',
        ],
        'host with user "0" and pass "0"' => [
            'https://0:0@0:1/0?0#0',
            '0:0@0:1',
        ],
        'host only and standard port' => [
            'https://site:443',
            'site',
        ],
        'host only and non standard port' => [
            'https://site:445',
            'site:445',
        ],
        'with user only and host and non standard port' => [
            'https://pi%20ter@site:445/index.html',
            'pi%20ter@site:445',
        ],
        'with user and password' => [
            'https://root:pass@site/dir',
            'root:pass@site',
        ],
        'with host IP4 and password with encoded symbols' => [
            'http://mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1:80/site/',
            'mary:%5Bmag%20ray%5D%3Apass%20word+%40@192.168.1.1',
        ],
        'with host IP6' => [
            'https://mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]:443/index.php',
            'mary:pass@[fe80::7fc5:a9c7:a82e:d24e%26]',
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
