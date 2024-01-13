<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\Unit\Message;

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[UsesClass(Stream::class)]
class MessageBodyTest extends TestCase
{
    public function testGetBody(): void
    {
        $m = new Message();
        $this->assertInstanceOf(StreamInterface::class, $m->getBody());
        $this->assertInstanceOf(Stream::class, $m->getBody());
    }

    public function testWithBody(): void
    {
        $m = new Message();
        $n = $m->withBody(new Stream(''));

        $this->assertNotSame($m, $n);
    }
}
