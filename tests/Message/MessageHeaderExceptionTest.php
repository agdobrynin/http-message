<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Message;

use InvalidArgumentException;
use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Kaspi\HttpMessage\DatasetHeaders;
use Tests\Kaspi\HttpMessage\StreamAdapter;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
class MessageHeaderExceptionTest extends TestCase
{
    #[DataProviderExternal(DatasetHeaders::class, 'notCompatibleRFC7230')]
    public function testHeaderNameMustBeRFC7230Compatible(string $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RFC 7230 compatible');

        (new Message(StreamAdapter::make('')))->withHeader($name, $value);
    }

    #[DataProviderExternal(DatasetHeaders::class, 'emptyValue')]
    public function testHeaderNameOrValuesIsEmptyValue(string $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');

        (new Message(StreamAdapter::make('')))->withHeader($name, $value);
    }

    public function testGetHeaderEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name is empty string');

        (new Message(StreamAdapter::make('')))->getHeader('');
    }
}
