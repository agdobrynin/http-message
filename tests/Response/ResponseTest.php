<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Response;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetReasonPhrase;

/**
 * @internal
 */
#[CoversClass(Response::class)]
#[CoversClass(Message::class)]
class ResponseTest extends TestCase
{
    public function testConstructorCodeAndStatusEmpty(): void
    {
        $r = new Response();

        self::assertEquals(200, $r->getStatusCode());
        self::assertEquals('OK', $r->getReasonPhrase());
    }

    public function testConstructorCodeAndStatusWithParams(): void
    {
        $r = new Response(201, 'will be ok');

        self::assertEquals(201, $r->getStatusCode());
        self::assertEquals('will be ok', $r->getReasonPhrase());
    }

    #[DataProvider('statusCode')]
    public function testFailStatusCodeInConstructor(int $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status code');

        new Response($code);
    }

    #[DataProvider('statusCode')]
    public function testFailWithStatusCode(int $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status code');

        (new Response())->withStatus($code);
    }

    public static function statusCode(): Generator
    {
        yield 'set #1' => [99];

        yield 'set #2' => [600];
    }

    public function testWithStatusCode(): void
    {
        $r = new Response();
        self::assertEquals(200, $r->getStatusCode());
        self::assertEquals('OK', $r->getReasonPhrase());

        $r1 = $r->withStatus(201);

        self::assertNotSame($r, $r1);
        self::assertEquals(201, $r1->getStatusCode());
        self::assertEquals('Created', $r1->getReasonPhrase());

        $r2 = $r1->withStatus(404, 'Sorry i am not found your document right now');
        self::assertNotSame($r2, $r1);
        self::assertEquals(404, $r2->getStatusCode());
        self::assertEquals('Sorry i am not found your document right now', $r2->getReasonPhrase());
    }

    #[DataProviderExternal(DatasetReasonPhrase::class, 'reasonPhraseSuccess')]
    public function testNormalizeReasonPhraseInWithStatus($reasonPhrase, $expect): void
    {
        $r = (new Response())->withStatus(100, $reasonPhrase);

        self::assertEquals($expect, $r->getReasonPhrase());
    }

    #[DataProviderExternal(DatasetReasonPhrase::class, 'reasonPhraseFail')]
    public function testFailReasonPhraseInWithStatus($reasonPhrase): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Response())->withStatus(100, $reasonPhrase);
    }
}
