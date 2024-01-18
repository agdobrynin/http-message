<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    public function getServerParams(): array
    {
        // TODO: Implement getServerParams() method.
    }

    public function getCookieParams(): array
    {
        // TODO: Implement getCookieParams() method.
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        // TODO: Implement withCookieParams() method.
    }

    public function getQueryParams(): array
    {
        // TODO: Implement getQueryParams() method.
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        // TODO: Implement withQueryParams() method.
    }

    public function getUploadedFiles(): array
    {
        // TODO: Implement getUploadedFiles() method.
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        // TODO: Implement withUploadedFiles() method.
    }

    public function getParsedBody()
    {
        // TODO: Implement getParsedBody() method.
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        // TODO: Implement withParsedBody() method.
    }

    public function getAttributes(): array
    {
        // TODO: Implement getAttributes() method.
    }

    public function getAttribute(string $name, $default = null)
    {
        // TODO: Implement getAttribute() method.
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        // TODO: Implement withAttribute() method.
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        // TODO: Implement withoutAttribute() method.
    }
}
