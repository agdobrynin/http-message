<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function call_user_func;

trait CreateStreamFromStringTrait
{
    /**
     * @var callable
     */
    private $streamResolver;

    public function setStreamResolver(callable $streamResolver): self
    {
        $this->streamResolver = $streamResolver;

        return $this;
    }

    public function streamFromString(string $body = ''): StreamInterface
    {
        if (!isset($this->streamResolver)) {
            throw new RuntimeException('Not define stream resolver');
        }

        if (!($stream = call_user_func($this->streamResolver)) instanceof StreamInterface) {
            throw new RuntimeException('Stream resolver must be implement '.StreamInterface::class);
        }

        if ('' !== $body) {
            $stream->write($body);
            $stream->rewind();
        }

        return $stream;
    }
}
