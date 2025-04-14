<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Stringable;

use function is_string;
use function ltrim;
use function parse_url;
use function preg_replace_callback;
use function rawurlencode;
use function str_starts_with;
use function strtolower;

class Uri implements UriInterface, Stringable
{
    /**
     * Regexp - for special parts of URI.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#appendix-A
     * unreserved = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * gen-delims = ":" / "/" / "?" / "#" / "[" / "]" / "@"
     * sub-delims = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
     */
    private const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
    private const CHAR_PCT_ENCODED = '%(?![A-Fa-f0-9]{2})';
    private const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';
    private const SCHEME_PORT = [
        'http' => 80,
        'https' => 443,
    ];

    private string $scheme = '';
    private string $host = '';
    private ?int $port = null;
    private string $user = '';
    private string $pass = '';
    private string $query = '';
    private string $path = '';
    private string $fragment = '';

    public function __construct(string $uri)
    {
        $values = parse_url($uri);

        if (false === $values) {
            throw new InvalidArgumentException("Invalid URI [{$uri}]");
        }

        foreach ($values as $key => $value) {
            $this->{$key} = match ($key) {
                'scheme', 'host' => strtolower($value),
                'path' => self::encode(EncodeEnum::path, $value),
                'fragment' => self::encode(EncodeEnum::fragment, $value),
                'query' => self::encode(EncodeEnum::query, $value),
                'pass', 'user' => self::encode(EncodeEnum::userinfo, $value),
                default => $value,
            };
        }
    }

    public function __toString(): string
    {
        $uri = '';

        if ($scheme = $this->getScheme()) {
            $uri .= $scheme.':';
        }

        $authority = $this->getAuthority();

        if ($authority) {
            $uri .= '//'.$authority;
        }

        if ('' !== ($path = $this->path)) {
            if ('' !== $authority && !str_starts_with($path, '/')) {
                $path = '/'.$path;
            } elseif ('' === $authority && str_starts_with($path, '//')) {
                $path = '/'.ltrim($path, '/');
            }

            $uri .= $path;
        }

        if ('' !== ($query = $this->getQuery())) {
            $uri .= '?'.$query;
        }

        if ('' !== ($fragment = $this->getFragment())) {
            $uri .= '#'.$fragment;
        }

        return $uri;
    }

    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        return ('' !== ($userInfo = $this->getUserInfo()) ? $userInfo.'@' : '')
            .$this->host
            .(null !== ($port = $this->getPort()) ? ':'.$port : '');
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
        if ('' !== $this->path && '' !== $this->host) {
            if (!str_starts_with($this->path, '/')) {
                return '/'.$this->path;
            }

            if (str_starts_with($this->path, '//')) {
                return '/'.ltrim($this->path, '/');
            }
        }

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

        return $this->user.('' !== $this->pass ? ':'.$this->pass : '');
    }

    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;
        $new->fragment = '' !== $fragment
            ? self::encode(EncodeEnum::fragment, $fragment)
            : '';

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $new = clone $this;
        $new->host = '' !== $host
            ? strtolower($host)
            : '';

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
            throw new InvalidArgumentException("Invalid port [{$port}]. Must be between 0 and 65535");
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;
        $new->query = '' !== $query
            ? self::encode(EncodeEnum::query, $query)
            : '';

        return $new;
    }

    public function withScheme($scheme): UriInterface
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string value');
        }

        $new = clone $this;
        $new->scheme = '' !== $scheme
            ? strtolower($scheme)
            : '';

        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;
        $new->user = '' !== $user
            ? self::encode(EncodeEnum::userinfo, $user)
            : '';
        $new->pass = '' !== $password && null !== $password
            ? self::encode(EncodeEnum::userinfo, $password)
            : '';

        return $new;
    }

    protected static function encode(EncodeEnum $encode, string $value): string
    {
        $pattern = match ($encode) {
            EncodeEnum::userinfo => '/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.'%]+|'.self::CHAR_PCT_ENCODED.')/',
            EncodeEnum::path => '/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.'%:@\/]+|'.self::CHAR_PCT_ENCODED.')/',
            EncodeEnum::fragment,
            EncodeEnum::query => '/(?:[^'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.'%:@\/\?]+|'.self::CHAR_PCT_ENCODED.')/',
        };

        return preg_replace_callback($pattern, static fn (array $matches) => rawurlencode($matches[0]), $value);
    }
}
