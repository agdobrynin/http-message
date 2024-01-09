<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected const SCHEME_PORT = [
        'http' => 80,
        'https' => 443,
    ];
    protected string $scheme = '';
    protected string $host = '';
    protected ?int $port = null;
    protected string $user = '';
    protected string $pass = '';
    protected string $query = '';
    protected string $path = '';
    protected string $fragment = '';

    public function __construct(string $uri)
    {
        $params = \parse_url($uri);

        if (false === $params) {
            throw new \InvalidArgumentException("Invalid URI [{$uri}]");
        }

        foreach ($params as $key => $param) {
            $this->{$key} = match ($param) {
                'scheme', 'host' => \strtolower($param),
                default => $param,
            };
        }
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        return '';
    }

    public function getAuthority(): string
    {
        // TODO: Implement getAuthority() method.
        return '';
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPort(): ?int
    {
        return ($portDefault = (static::SCHEME_PORT[$this->scheme] ?? null))
                && $portDefault === $this->port
                    ? null
                    : $this->port;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUserInfo(): string
    {
        // TODO: Implement getUserInfo() method.
        return '';
    }

    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;
        // TODO escaping
        $new->fragment = $fragment;

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $new = clone $this;
        $new->host = \strtolower($host);

        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        $new = clone $this;
        // TODO escaping
        $new->path = $path;

        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        if (null !== $port && ($port < 0 || $port > 65535)) {
            throw new \InvalidArgumentException("Invalid port [{$port}]. Must be between 0 and 65535");
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;
        // TODO escaping
        $new->query = $query;

        return $new;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $new = clone $this;
        $new->scheme = \strtolower($scheme);

        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;
        // TODO escaping
        $this->user = $user;
        // TODO escaping
        $this->pass = (string) $password;

        return $new;
    }
}
