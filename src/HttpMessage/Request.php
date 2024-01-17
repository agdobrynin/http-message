<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected string $method;
    protected UriInterface $uri;
    protected ?string $requestTarget = null;

    public function __construct(string $method, string|UriInterface $uri)
    {
        $this->method = $method;
        $this->uri = \is_string($uri) ? new Uri($uri) : $uri;
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        $pathNormalize = ($path = $this->uri->getPath()) === ''
            ? '/'
            : $path;
        $queryNormalize = ($query = $this->uri->getQuery()) === ''
            ? $query
            : '?'.$query;

        return $pathNormalize.$queryNormalize;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (\str_contains($requestTarget, ' ')) {
            throw new \InvalidArgumentException('Request target cannot contain whitespace');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        // TODO Implement later.
    }
}
