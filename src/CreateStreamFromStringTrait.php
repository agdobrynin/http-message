<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Kaspi\HttpMessage\Stream\PhpTempStream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function call_user_func;
use function get_class;

trait CreateStreamFromStringTrait
{
    public static function streamFromString(string $body = '', ?callable $streamResolver = null): StreamInterface
    {
        if (null === $streamResolver) {
            $stream = new PhpTempStream();
        } else {
            if (!($stream = call_user_func($streamResolver)) instanceof StreamInterface) {
                throw new RuntimeException('Stream resolver must be implement '.StreamInterface::class.'. Got: '.get_class($stream));
            }
        }

        if ('' !== $body) {
            $stream->write($body);
            $stream->rewind();
        }

        return $stream;
    }
}
