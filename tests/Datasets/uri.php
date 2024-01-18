<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Uri;

\dataset('uri_success', [
    'from string' => [
        'uri' => 'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
        'uriExpect' => 'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
    ],
    'from Uri' => [
        'uri' => new Uri('https://www.php.org/index.php?q=list&order=desc#fig1-6.1'),
        'uriExpect' => 'https://www.php.org/index.php?q=list&order=desc#fig1-6.1',
    ],
    'from Uri components' => [
        'uri' => (new Uri('/'))->withQuery('abc=2&lis[m')->withHost('php.net')->withPort(8080),
        'uriExpect' => '//php.net:8080/?abc=2&lis%5Bm',
    ],
]);
