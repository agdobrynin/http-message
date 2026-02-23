<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;
use Kaspi\HttpMessage\HttpFactory;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\Response;
use Kaspi\HttpMessage\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(HttpFactory::class)]
#[CoversClass(Message::class)]
#[CoversClass(Request::class)]
#[CoversClass(Response::class)]
#[CoversClass(Uri::class)]
class HttpFactoryTest extends TestCase
{
    #[DataProviderExternal(Dataset::class, 'httpFactoryRequest')]
    public function testMethodAndURI($method, $uri, $expectUri): void
    {
        $r = (new HttpFactory())->createRequest($method, $uri);

        self::assertEquals($method, $r->getMethod());
        self::assertEquals($expectUri, $r->getUri());
    }

    #[DataProvider('dataProviderHttpStatusCodeAndResponsePhrase')]
    public function testHttpStatusCodeAndResponsePhrase(array $args, $expectCode, $expectPhrase): void
    {
        $r = (new HttpFactory())->createResponse(...$args);

        self::assertEquals($expectCode, $r->getStatusCode());
        self::assertEquals($expectPhrase, $r->getReasonPhrase());
    }

    public static function dataProviderHttpStatusCodeAndResponsePhrase(): Generator
    {
        yield 'all default' => [
            [],
            200,
            'OK',
        ];

        yield 'standard http status 404' => [
            [404],
            404,
            'Not Found',
        ];

        yield 'standard http status 599' => [
            [599],
            599,
            '',
        ];

        yield 'standard http status 511' => [
            [511],
            511,
            'Network Authentication Required',
        ];

        yield 'standard http status and custom response phrase' => [
            [201, 'Account created success. You can login now.'],
            201,
            'Account created success. You can login now.',
        ];
    }
}
