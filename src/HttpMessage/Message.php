<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    private string $version = '1.1';
    private array $headers = [];
    private StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withProtocolVersion(string $version): static
    {
        if (\preg_match('/^\d+\.\d+$/', $version)) {
            $new = clone $this;
            $new->version = $version;

            return $new;
        }

        throw new \InvalidArgumentException('Protocol must be implement "<major>.<minor>" numbering scheme');
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('Header name is empty string');
        }

        return \array_key_exists(\strtolower($name), $this->headers);
    }

    public function getHeader(string $name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        return $this->headers[\strtolower($name)];
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
        $normalizedName = \strtolower($name);
        $hasHeader = $this->hasHeader($normalizedName);
        $value = $this->validateRFC7230AndTrim($normalizedName, $value);
        $new = clone $this;

        if ($hasHeader) {
            unset($new->headers[$normalizedName]);
        }

        $new->headers[$normalizedName] = $value;

        return $new;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withAddedHeader(string $name, mixed $value): static
    {
        $normalizedName = \strtolower($name);
        $hasHeader = $this->hasHeader($normalizedName);
        $value = $this->validateRFC7230AndTrim($normalizedName, $value);

        $new = clone $this;

        if ($hasHeader) {
            $new->headers[$normalizedName] = \array_merge($this->headers[$normalizedName], $value);
        } else {
            $new->headers[$normalizedName] = $value;
        }

        return $new;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withoutHeader(string $name): static
    {
        $normalizedName = \strtolower($name);

        if (!$this->hasHeader($normalizedName)) {
            return $this;
        }

        $new = clone $this;
        unset($new->headers[$normalizedName]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        if (!isset($this->body)) {
            $this->body = new Stream('');
        }

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

        $valuesRaw = \array_filter(!\is_array($values) ? [$values] : $values);

        if ([] === $valuesRaw) {
            throw new \InvalidArgumentException('Header values must be non empty string');
        }

        $result = [];

        foreach ($valuesRaw as $value) {
            if ((!\is_numeric($value) && !\is_string($value))
                || 1 !== \preg_match('/^[ \t\x21-\x7E\x80-\xFF]*$/', (string) $value)) {
                $val = \var_export($value, true);

                throw new \InvalidArgumentException('Header value must be RFC 7230 compatible. Got: '.$val);
            }

            $result[] = ($v = \trim((string) $value, " \t"))
                ? $v
                : throw new \InvalidArgumentException('Header values must be a non empty string');
        }

        return $result;
    }
}
