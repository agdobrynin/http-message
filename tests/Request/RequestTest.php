<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Request;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Tests\Kaspi\HttpMessage\DatasetHeaders;

use function is_string;

/**
 * @internal
 */
#[CoversClass(Request::class)]
#[CoversClass(Message::class)]
#[CoversClass(Uri::class)]
class RequestTest extends TestCase
{
    #[DataProvider('dataProviderConstructor')]
    public function testConstructor($method, $uri): void
    {
        $r = new Request($method, $uri);

        self::assertEquals($method, $r->getMethod());

        if ($uri instanceof UriInterface) {
            self::assertEquals($uri->getHost(), $r->getUri()->getHost());
        }

        if (is_string($uri)) {
            self::assertEquals($uri, (string) $r->getUri());
        }
    }

    public static function dataProviderConstructor(): Generator
    {
        yield 'all string' => ['GET', 'https://www.com/ok'];

        yield 'Uri as object' => ['POST', new Uri('https://www.baz.com/ok/?x=1')];
    }

    #[DataProvider('dataProviderRequestTarget')]
    public function testRequestTarget($uri, string $requestTarget): void
    {
        $r = new Request('GET', $uri);

        self::assertEquals($requestTarget, $r->getRequestTarget());
    }

    public static function dataProviderRequestTarget(): Generator
    {
        yield 'empty' => ['', '/'];

        yield 'uri without path' => [
            'http://www.com', '/',
        ];

        yield 'uri with path' => [
            'http://www.com/test-of list.doc#section5.2', '/test-of%20list.doc',
        ];

        yield 'uri without path and with query string' => [
            'http://www.com?x=10#list$', '/?x=10',
        ];
    }

    #[DataProvider('dataProviderWithRequestTarget')]
    public function testWithRequestTarget(RequestInterface $request, string $requestTarget, string $expectRequestTarget): void
    {
        $r = $request->withRequestTarget($requestTarget);

        self::assertNotSame($request, $r);
        self::assertEquals($expectRequestTarget, $r->getRequestTarget());
    }

    public static function dataProviderWithRequestTarget(): Generator
    {
        yield 'request target is empty string' => [
            new Request('GET', ''),
            '',
            '/',
        ];

        yield 'request target changed' => [
            new Request('GET', 'http://foo.baz/list'),
            '*',
            '*',
        ];
    }

    #[DataProvider('dataProviderWithRequestTargetException')]
    public function testWithRequestTargetException(string $requestTarget): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contain whitespace');

        (new Request('GET', 'http://www.com/index.php'))
            ->withRequestTarget($requestTarget)
        ;
    }

    public static function dataProviderWithRequestTargetException(): Generator
    {
        yield 'whitespace' => [' '];

        yield 'whitespace in middle' => ['ok /index.ogo'];

        yield 'whitespace in end' => ['/index '];

        yield 'whitespace and asterisk' => [' * '];
    }

    #[DataProvider('dataProviderWithMethod')]
    public function testWithMethod(RequestInterface $request, string $method): void
    {
        $r = $request->withMethod($method);

        self::assertNotSame($request, $r);
        self::assertEquals($method, $r->getMethod());
    }

    public static function dataProviderWithMethod(): Generator
    {
        yield 'from GET to POST' => [new Request('GET', ''), 'POST'];

        yield 'from GET to OPTION' => [new Request('GET', ''), 'OPTION'];
    }

    public function testWithMethodException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method should non-empty string');

        $request = new Request('GET', '');
        $request->withMethod('');
    }

    #[DataProvider('dataProviderWithUri')]
    public function testWithUri(RequestInterface $request, UriInterface $uri, bool $preserveHost, string $expectHost): void
    {
        $r = $request->withUri($uri, $preserveHost);

        self::assertNotSame($r, $request);
        self::assertEquals($expectHost, $r->getHeaderLine('host'));

        self::assertEquals(['Host' => [$expectHost]], $r->getHeaders());
    }

    public static function dataProviderWithUri(): Generator
    {
        yield 'init host is empty' => [
            new Request('GET', '?param=2'),
            new Uri('//site.php.org/index.php?query=abc'),
            false,
            'site.php.org',
        ];

        yield 'init host non-empty with preserveHost=true' => [
            new Request('GET', '//php.net/?p=1#f1.6'),
            new Uri('//site.php.org/index.php?query=abc'),
            true,
            'php.net',
        ];

        yield 'with host non standard port' => [
            new Request('GET', 'http://list.com:8486'),
            new Uri('//site.php.org:8080/index.php?query=abc'),
            false,
            'site.php.org:8080',
        ];

        yield 'with host standard port as value in URI' => [
            new Request('GET', ''),
            new Uri('https://site.php.org:443/index.php?query=abc'),
            false,
            'site.php.org',
        ];

        yield 'init with host non-standard port and URI with non-standard port' => [
            new Request('GET', 'http://www.msn.net:8080'),
            new Uri('https://site.php.org:444/index.php?query=abc'),
            false,
            'site.php.org:444',
        ];
    }

    public function testAfterApplyWithHostHeaderHostShouldFirstItem(): void
    {
        $request = (new Request('POST', '/'))
            ->withHeader('expire', 'today')
            ->withAddedHeader('Cache-Control', 'public')
            ->withAddedHeader('Cache-Control', 'max-age=14400')
        ;

        self::assertEquals(
            [
                'expire' => ['today'],
                'Cache-Control' => ['public', 'max-age=14400'],
            ],
            $request->getHeaders()
        );

        $r = $request->withUri(new Uri('http://www.php.net/doc/index.php?list=desc&limit=10#section 1.2'));

        self::assertNotSame($r, $request);
        self::assertEquals(
            [
                'Host' => ['www.php.net'],
                'expire' => ['today'],
                'Cache-Control' => ['public', 'max-age=14400'],
            ],
            $r->getHeaders()
        );
        self::assertEquals('list=desc&limit=10', $r->getUri()->getQuery());
        self::assertEquals('section%201.2', $r->getUri()->getFragment());
    }

    #[DataProviderExternal(DatasetHeaders::class, 'headersWithUri')]
    public function testHeaderHostShouldFirstItemInPresentInURI($uri, $headers, $expectHeaders): void
    {
        $request = new Request(
            method: 'POST',
            uri: $uri,
            headers: $headers
        );

        self::assertEquals($expectHeaders, $request->getHeaders());
    }

    public function testHeaderInRequestWithEmptyValue(): void
    {
        $h = (new Request('GET', 'http://php.org/'))
            ->withHeader('fix', '')
            ->getHeader('fix')
        ;

        self::assertEquals([''], $h);
    }
}
