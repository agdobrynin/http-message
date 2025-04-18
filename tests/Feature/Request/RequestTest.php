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
        'all string' => ['GET', 'https://www.com/ok'],
        'Uri as object' => ['POST', new Uri('https://www.baz.com/ok/?x=1')],
    ]);

    \it('request target', function ($uri, string $requestTarget) {
        $r = new Request('GET', $uri);

        \expect($r->getRequestTarget())->toBe($requestTarget);
    })->with([
        'empty' => ['', '/'],
        'uri without path' => [
            'http://www.com', '/',
        ],
        'uri with path' => [
            'http://www.com/test-of list.doc#section5.2', '/test-of%20list.doc',
        ],
        'uri without path and with query string' => [
            'http://www.com?x=10#list$', '/?x=10',
        ],
    ]);

    \it('withRequestTarget', function (RequestInterface $request, string $requestTarget, string $expectRequestTarget) {
        $r = $request->withRequestTarget($requestTarget);

        \expect($r)->not->toBe($request)
            ->and($r->getRequestTarget())->toBe($expectRequestTarget)
        ;
    })->with([
        'request target is empty string' => [
            new Request('GET', ''),
            '',
            '/',
        ],
        'request target changed' => [
            new Request('GET', 'http://foo.baz/list'),
            '*',
            '*',
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
        'from GET to OPTION' => [new Request('GET', ''), 'OPTION'],
    ]);

    \it('withMethod exception', function (RequestInterface $request, $method) {
        try {
            $request->withMethod($method);
        } catch (InvalidArgumentException|TypeError $exception) {
            \expect($exception instanceof Throwable)->toBeTrue();
        }
    })
        ->with([
            'empty string' => [new Request('GET', ''), ''],
            'as null value' => [new Request('GET', ''), null],
            'as object value' => [new Request('GET', ''), (object) []],
            'as class value' => [new Request('GET', ''), new Request()],
        ])
    ;

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
            new Request('GET', '?param=2'),
            new Uri('//site.php.org/index.php?query=abc'),
            false,
            'site.php.org',
        ],
        'init host non-empty with preserveHost=true' => [
            new Request('GET', '//php.net/?p=1#f1.6'),
            new Uri('//site.php.org/index.php?query=abc'),
            true,
            'php.net',
        ],
        'with host non standard port' => [
            new Request('GET', 'http://list.com:8486'),
            new Uri('//site.php.org:8080/index.php?query=abc'),
            false,
            'site.php.org:8080',
        ],
        'with host standard port as value in URI' => [
            new Request('GET', ''),
            new Uri('https://site.php.org:443/index.php?query=abc'),
            false,
            'site.php.org',
        ],
        'init with host non-standard port and URI with non-standard port' => [
            new Request('GET', 'http://www.msn.net:8080'),
            new Uri('https://site.php.org:444/index.php?query=abc'),
            false,
            'site.php.org:444',
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

    \it('Header in request with empty value', function () {
        \expect(
            (new Request('GET', 'http://php.org/'))
                ->withHeader('fix', '')
                ->getHeader('fix')
        )->toBe(['']);
    });
})->covers(Request::class, Uri::class, Message::class, Stream::class);
