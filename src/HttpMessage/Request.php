<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected ?string $requestTarget = null;
    private string $method;
    private UriInterface $uri;

    /**
     * @param \Psr\Http\Message\StreamInterface|resource|string $body
     */
    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '',
        $body = '',
        array $headers = [],
        string $protocolVersion = '1.1'
    ) {
        parent::__construct($body, $headers, $protocolVersion);
        $this->method = $method;
        $this->uri = \is_string($uri) ? new Uri($uri) : $uri;

        parent::updateHostFromUri($this->uri);
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

    // @phan-suppress-next-line PhanParamSignatureMismatch
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

    // @phan-suppress-next-line PhanParamSignatureMismatch
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

    // @phan-suppress-next-line PhanParamSignatureMismatch
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $new->updateHostFromUri($uri);
        }

        return $new;
    }
}
