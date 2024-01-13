<?php

declare(strict_types=1);

namespace Message;

use Kaspi\HttpMessage\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Message::class)]
class MessageProtocolTest extends TestCase
{
    public function testProtocolVersion(): void
    {
        $this->assertEquals('1.1', (new Message())->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $m = new Message();
        $n = $m->withProtocolVersion('1.2');

        $this->assertEquals('1.2', $n->getProtocolVersion());
        $this->assertEquals('1.1', $m->getProtocolVersion());
        $this->assertNotSame($m, $n);
    }

    public function testWithProtocolVersionException(): void
    {
        $m = new Message();

        $this->expectException(\InvalidArgumentException::class);

        $m->withProtocolVersion('1');
    }
}
