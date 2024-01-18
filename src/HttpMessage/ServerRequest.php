<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    private array $cookies = [];
    private array $query = [];

    /**
     * @var \Psr\Http\Message\UploadedFileInterface[]
     */
    private array $uploadedFiles = [];

    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '',
        $body = '',
        array $headers = [],
        string $protocolVersion = '1.1',
        private readonly array $serverParams = [],
    ) {
        parent::__construct($method, $uri, $body, $headers, $protocolVersion);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookies = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->query;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookies = $query;

        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $this->uploadedFiles = \array_filter(
            $uploadedFiles,
            static function ($item) {
                return $item instanceof UploadedFileInterface
                    ?: throw new \InvalidArgumentException('Items must be instance of '.UploadedFileInterface::class);
            }
        );
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
