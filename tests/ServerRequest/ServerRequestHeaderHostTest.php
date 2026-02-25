<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\ServerRequest;

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ServerRequest::class)]
#[CoversClass(Uri::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
class ServerRequestHeaderHostTest extends TestCase
{
    // @see https://www.php-fig.org/psr/psr-7/#host-header

    public function testHeaderIsEmptyUriIsEmptyWithUriEmpty(): void
    {
        $sr = (new ServerRequest(
            uri: new Uri('')
        ))->withUri(new Uri(''), true);

        self::assertEquals('', $sr->getHeaderLine('Host'));
    }

    public function testHeaderIsEmptyUriHasHostWithUriIsEmpty(): void
    {
        $sr = (new ServerRequest(uri: new Uri('https://foo.com')))
            ->withUri(new Uri(''), true)
        ;

        self::assertEquals('foo.com', $sr->getHeaderLine('Host'));
    }

    public function testHeaderIsEmptyUriHasHostWithUriHasHost(): void
    {
        $sr = (new ServerRequest(uri: new Uri('https://foo.com')))
            ->withUri(new Uri('https://bar.com'), true)
        ;

        self::assertEquals('foo.com', $sr->getHeaderLine('Host'));
    }

    public function testHeaderHasHostUriIsEmptyWithUriHasHost(): void
    {
        $sr = (new ServerRequest(uri: new Uri(''), headers: ['Host' => ['foo.com']]))
            ->withUri(new Uri('https://bar.com'), true)
        ;

        self::assertEquals('foo.com', $sr->getHeaderLine('Host'));
    }

    public function testHeaderHasHostUriHasHostWithUriHasHost(): void
    {
        $sr = (new ServerRequest(uri: new Uri('https://bar.com'), headers: ['Host' => ['foo.com']]))
            ->withUri(new Uri('https://baz.com'), true)
        ;

        self::assertEquals('foo.com', $sr->getHeaderLine('Host'));
    }
}
