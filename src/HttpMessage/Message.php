<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Message implements MessageInterface
{
    public const RFC7230_FIELD_TOKEN = '/^[\x09\x20-\x7E\x80-\xFF]*$/';

    private string $version;
    private array $headers = [];

    public function __construct(private StreamInterface $body, array $headers = [], string $protocolVersion = '1.1')
    {
        $this->addHeaders($headers);
        $this->version = $protocolVersion;
    }

    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->version = $version;

        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return null !== $this->getHeaderByName($name);
    }

    public function getHeader(string $name): array
    {
        return null !== ($h = $this->getHeaderByName($name))
            ? $this->headers[$h]
            : [];
    }

    public function getHeaderLine(string $name): string
    {
        return \implode(', ', $this->getHeader($name));
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withHeader(string $name, mixed $value): static
    {
        $new = clone $this;

        if (($h = $this->getHeaderByName($name)) !== null) {
            unset($new->headers[$h]);
        }

        $new->headers[$name] = $this->validateRFC7230AndTrim($name, $value);

        return $new;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withAddedHeader(string $name, mixed $value): static
    {
        $new = clone $this;
        $new->addHeaders([$name => $value]);

        return $new;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withoutHeader(string $name): static
    {
        if (($h = $this->getHeaderByName($name)) !== null) {
            $new = clone $this;
            unset($new->headers[$h]);

            return $new;
        }

        return $this;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    protected function updateHostFromUri(UriInterface $uri): void
    {
        if ('' !== ($host = $uri->getHost())) {
            if (null !== ($h = $this->getHeaderByName('host'))) {
                unset($this->headers[$h]);
            }

            // The header "Host" SHOULD first item in headers.
            // @see https://datatracker.ietf.org/doc/html/rfc7230#section-5.4
            $this->headers = \array_merge(
                ['Host' => [$host.(($port = $uri->getPort()) ? ':'.$port : '')]],
                $this->headers
            );
        }
    }

    private function addHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            /*
             * A array key may be either an integer or a string.
             * For string such as "1", "2" (numeric) php casting array key as integer
             * @see https://www.php.net/manual/en/language.types.array.php
             */
            if (\is_int($name)) {
                $name = (string) $name;
            }

            $value = $this->validateRFC7230AndTrim($name, $value);

            if (($h = $this->getHeaderByName($name)) !== null) {
                $this->headers[$h] = \array_merge($this->headers[$h], $value);
            } else {
                $this->headers[$name] = $value;
            }
        }
    }

    /**
     * Return value of header name may be as integer or string.
     * For example header name '0' always store in array key as integer value.
     */
    private function getHeaderByName(string $name): null|int|string
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('Header name is empty string');
        }

        return ($h = \preg_grep('/^'.\preg_quote($name, '').'$/i', \array_keys($this->headers)))
            ? \current($h)
            : null;
    }

    /**
     *  Header
     *  -------
     *  token =  1*tchar
     *  tchar =  "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." / "^" / "_" / "`" / "|" / "~"
     *           / DIGIT / ALPHA
     *           ; any VCHAR, except delimiters.
     *
     *  Value of header
     *  -----
     *  A string of text is parsed as a single value if it is quoted using
     *  double-quote marks.
     *
     *  quoted-string = DQUOTE *( qdtext / quoted-pair ) DQUOTE
     *  qdtext        = HTAB / SP /%x21 / %x23-5B / %x5D-7E / obs-text
     *  obs-text      = %x80-FF
     *  quoted-pair   = "\" ( HTAB / SP / VCHAR / obs-text
     *
     * Comments can be included in some HTTP header fields by surrounding
     * the comment text with parentheses.  Comments are only allowed in
     * fields containing "comment" as part of their field value definition.
     *
     * comment        = "(" *( ctext / quoted-pair / comment ) ")"
     * ctext          = HTAB / SP / %x21-27 / %x2A-5B / %x5D-7E / obs-text
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.6
     */
    private function validateRFC7230AndTrim(string $header, mixed $values): array
    {
        if ('' === $header
            || 1 !== \preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/D', $header)) {
            throw new \InvalidArgumentException('Header name must be RFC 7230 compatible');
        }

        $valuesRaw = !\is_array($values) ? [$values] : $values;

        if ([] === $valuesRaw) {
            throw new \InvalidArgumentException('Header values must be non-empty array');
        }

        $result = [];

        foreach ($valuesRaw as $value) {
            if ((!\is_numeric($value) && !\is_string($value))
                || 1 !== \preg_match(self::RFC7230_FIELD_TOKEN, (string) $value)) {
                $val = \var_export($value, true);

                throw new \InvalidArgumentException('Header value must be RFC 7230 compatible. Got: '.$val);
            }

            $result[] = \trim((string) $value, " \t");
        }

        return $result;
    }
}
