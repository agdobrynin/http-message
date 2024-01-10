<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Regexp - for special parts of URI.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#appendix-A
     * unreserved = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * gen-delims = ":" / "/" / "?" / "#" / "[" / "]" / "@"
     * sub-delims = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
     */
    protected const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
    protected const CHAR_PCT_ENCODED = '%(?![A-Fa-f0-9]{2})';
    protected const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';
    protected const CHAR_BASE = self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS;

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
        $values = \parse_url($uri);

        if (false === $values) {
            throw new \InvalidArgumentException("Invalid URI [{$uri}]");
        }

        // TODO may be check the uri string is valid here?

        foreach ($values as $key => $value) {
            $this->{$key} = match ($key) {
                'scheme', 'host' => \strtolower($value),
                default => $value,
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
        if ('' === $this->host) {
            return '';
        }

        return (($userInfo = $this->getUserInfo()) ? $userInfo.'@' : '').
            $this->host.
            (($port = $this->getPort()) ? ':'.$port : '');
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
        if ('' === $this->user) {
            return '';
        }

        return $this->user.($this->pass ? ':'.$this->pass : '');
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
        $new->path = '' !== $path
            ? self::encode(EncodeEnum::path, $path)
            : '';

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
        $new->user = $user
            ? self::encode(EncodeEnum::userinfo, $user)
            : '';
        $new->pass = !empty($password)
            ? self::encode(EncodeEnum::userinfo, $password)
            : '';

        return $new;
    }

    protected static function encode(EncodeEnum $encode, string $value): string
    {
        $pattern = match ($encode) {
            EncodeEnum::userinfo => '/(?:[^'.self::CHAR_BASE.'%]++|'.self::CHAR_PCT_ENCODED.')/',
            EncodeEnum::path => '/(?:[^'.self::CHAR_BASE.'%:@\/]++|'.self::CHAR_PCT_ENCODED.')/',
            EncodeEnum::query,
            EncodeEnum::fragment => '/(?:[^'.self::CHAR_BASE.'%:@\/\?]++|'.self::CHAR_PCT_ENCODED.')/',
        };

        return \preg_replace_callback($pattern, static fn (array $matches) => \rawurlencode($matches[0]), $value);
    }
}
