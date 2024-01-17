<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    public function getStatusCode(): int
    {
        // TODO: Implement getStatusCode() method.
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        // TODO: Implement withStatus() method.
    }

    public function getReasonPhrase(): string
    {
        // TODO: Implement getReasonPhrase() method.
    }
}
