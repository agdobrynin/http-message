<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

\describe('Tests for '.Request::class, function () {
    \it('Constructor', function ($method, $uri) {
        $r = new Request($method, $uri);

        \expect($r->getMethod())->toBe($method);
        \expect($r->getUri())->toBeInstanceOf(UriInterface::class);

        if ($uri instanceof UriInterface) {
            \expect($r->getUri()->getHost())->toBe($uri->getHost());
        }

        if (\is_string($uri)) {
            \expect($uri)->toContain($r->getUri()->getHost());
        }
    })->with([
        'all string' => ['method' => 'GET', 'https://www.com/ok'],
        'Uri as object' => ['method' => 'POST', new Uri('https://www.baz.com/ok/?x=1')],
    ]);

    \it('request target', function ($uri, string $requestTarget) {
        $r = new Request('GET', $uri);

        \expect($r->getRequestTarget())->toBe($requestTarget);
    })->with([
        'empty' => ['', '/'],
        'uri without path' => [
            'uri' => 'http://www.com', 'requestTarget' => '/',
        ],
        'uri with path' => [
            'uri' => 'http://www.com/test-of list.doc#section5.2', 'requestTarget' => '/test-of%20list.doc',
        ],
        'uri without path and with query string' => [
            'uri' => 'http://www.com?x=10#list$', 'requestTarget' => '/?x=10',
        ],
    ]);

    \it('withRequestTarget', function (RequestInterface $request, string $requestTarget, string $expectRequestTarget) {
        $r = $request->withRequestTarget($requestTarget);

        \expect($r)->not->toBe($request)
            ->and($r->getRequestTarget())->toBe($expectRequestTarget)
        ;
    })->with([
        'request target is empty string' => [
            'request' => new Request('GET', ''),
            'requestTarget' => '',
            'expectRequestTarget' => '/',
        ],
        'request target changed' => [
            'request' => new Request('GET', 'http://foo.baz/list'),
            'requestTarget' => '*',
            'expectRequestTarget' => '*',
        ],
    ]);

    \it('withRequestTarget exception', function (string $requestTarget) {
        (new Request('GET', 'http://www.com/index.php'))
            ->withRequestTarget($requestTarget)
        ;
    })
        ->throws(InvalidArgumentException::class, 'contain whitespace')
        ->with([
            'whitespace' => [' '],
            'whitespace in middle' => ['ok /index.ogo'],
            'whitespace in end' => ['/index '],
            'whitespace and asterisk' => [' * '],
        ])
    ;

    \it('withMethod', function (RequestInterface $request, string $method) {
        $r = $request->withMethod($method);

        \expect($request)->not->toBe($r)
            ->and($r->getMethod())->toBe($method)
        ;
    })->with([
        'from GET to POST' => [new Request('GET', ''), 'POST'],
        'from GET to empty' => [new Request('GET', ''), ''],
    ]);

    \it('withUri', function (
        RequestInterface $request,
        UriInterface $uri,
        bool $preserveHost,
        string $expectHost,
    ) {
        $r = $request->withUri($uri, $preserveHost);

        \expect($r)->not->toBe($request)
            ->and($r->getHeaderLine('host'))->toBe($expectHost)
        ;
        \expect($r->getHeaders())->toBe([
            'Host' => [$expectHost],
        ]);
    })->with([
        'init host is empty' => [
            'request' => new Request('GET', '?param=2'),
            'uri' => new Uri('//site.php.org/index.php?query=abc'),
            'preserveHost' => false,
            'expectHost' => 'site.php.org',
        ],
        'init host non-empty with preserveHost=true' => [
            'request' => new Request('GET', '//php.net/?p=1#f1.6'),
            'uri' => new Uri('//site.php.org/index.php?query=abc'),
            'preserveHost' => true,
            'expectHost' => 'php.net',
        ],
        'with host non standard port' => [
            'request' => new Request('GET', 'http://list.com:8486'),
            'uri' => new Uri('//site.php.org:8080/index.php?query=abc'),
            'preserveHost' => false,
            'expectHost' => 'site.php.org:8080',
        ],
        'with host standard port as value in URI' => [
            'request' => new Request('GET', ''),
            'uri' => new Uri('https://site.php.org:443/index.php?query=abc'),
            'preserveHost' => false,
            'expectHost' => 'site.php.org',
        ],
        'init with host non-standard port and URI with non-standard port' => [
            'request' => new Request('GET', 'http://www.msn.net:8080'),
            'uri' => new Uri('https://site.php.org:444/index.php?query=abc'),
            'preserveHost' => false,
            'expectHost' => 'site.php.org:444',
        ],
    ]);

    \it('after apply withHost header Host should first item', function () {
        $request = (new Request('POST', '/'))
            ->withHeader('expire', 'today')
            ->withAddedHeader('Cache-Control', 'public')
            ->withAddedHeader('Cache-Control', 'max-age=14400')
        ;

        \expect($request->getHeaders())
            ->toBe([
                'expire' => ['today'],
                'Cache-Control' => ['public', 'max-age=14400'],
            ])
        ;

        $r = $request->withUri(new Uri('http://www.php.net/doc/index.php?list=desc&limit=10#section 1.2'));

        \expect($r)->not->toBe($request)
            ->and($r->getHeaders())
            ->toBe([
                'Host' => ['www.php.net'],
                'expire' => ['today'],
                'Cache-Control' => ['public', 'max-age=14400'],
            ])
            ->and($r->getUri()->getQuery())->toBe('list=desc&limit=10')
            ->and($r->getUri()->getFragment())->toBe('section%201.2')
        ;
    });

    \it('header Host should first item in present in URI', function ($uri, $headers, $expectHeaders) {
        $request = new Request(
            method: 'POST',
            uri: $uri,
            headers: $headers
        );

        \expect($request->getHeaders())->toBe($expectHeaders);
    })
        ->with('headers_with_uri')
    ;
})->covers(Request::class, Uri::class, Message::class, Stream::class);
