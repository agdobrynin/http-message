<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\ServerRequest;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;

/**
 * @internal
 */
#[CoversClass(ServerRequest::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
#[CoversClass(Uri::class)]
class ServerRequestTest extends TestCase
{
    public function testGetCookieParamsWithCookieParams(): void
    {
        $sr = new ServerRequest();

        self::assertEquals([], $sr->getCookieParams());

        $sr2 = $sr->withCookieParams(['q' => 'post', ['x' => [1, 2]]]);

        self::assertNotSame($sr2, $sr);
        self::assertEquals(['q' => 'post', ['x' => [1, 2]]], $sr2->getCookieParams());
    }

    public function testGetQueryParamsWithQueryParams(): void
    {
        $sr = new ServerRequest();

        self::assertEquals([], $sr->getQueryParams());

        $sr2 = $sr->withQueryParams(['q' => 'post', ['x' => [1, 2]]]);

        self::assertNotSame($sr2, $sr);

        self::assertEquals(['q' => 'post', ['x' => [1, 2]]], $sr2->getQueryParams());
    }

    #[DataProvider('successParsedBody')]
    public function testSuccessCallGetParsedBodyWithParsedBody($parsedBody): void
    {
        $sr = new ServerRequest();

        self::assertNull($sr->getParsedBody());

        $sr2 = $sr->withParsedBody($parsedBody);

        self::assertNotSame($sr2, $sr);
        self::assertEquals($parsedBody, $sr2->getParsedBody());
    }

    public static function successParsedBody(): Generator
    {
        yield 'null' => [null];

        yield 'array' => [['hello' => 'world']];

        yield 'object' => [(object) ['hello' => 'world']];

        yield 'object as class' => [new stdClass()];
    }

    #[DataProvider('failParsedBody')]
    public function testFailCallWithParsedBody($parsedBody): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ServerRequest())->withParsedBody($parsedBody);
    }

    public static function failParsedBody(): Generator
    {
        yield 'int' => [10];

        yield 'string' => ['Hi!'];

        yield 'float' => [3.14];

        yield 'boolean' => [false];

        yield 'resource' => [fopen(vfsStream::newFile('f')->at(vfsStream::setup())->url(), 'rb')];
    }

    public function testGetAttributesGetAttributeWithAttributeWithoutAttribute(): void
    {
        $sr = new ServerRequest();
        $name = 'hello';
        $value = ['world', 'php'];

        self::assertEquals([], $sr->getAttributes());
        self::assertEquals(100, $sr->getAttribute('no-attr', 100));

        $sr2 = $sr->withAttribute($name, $value);

        self::assertNotSame($sr, $sr2);
        self::assertEquals($value, $sr2->getAttribute($name));

        $sr3 = $sr2->withoutAttribute($name);
        self::assertNotSame($sr2, $sr3);

        self::assertEquals([], $sr3->getAttributes());
    }
}
