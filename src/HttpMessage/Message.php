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
        return (bool) $this->getHeaderByName($name);
    }

    public function getHeader(string $name): array
    {
        return $this->getHeaderByName($name)
            ? $this->headers[$name]
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

        if ($h = $this->getHeaderByName($name)) {
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
        $value = $this->validateRFC7230AndTrim($name, $value);

        $new = clone $this;

        if ($h = $this->getHeaderByName($name)) {
            $new->headers[$h] = \array_merge($this->headers[$h], $value);
        } else {
            $new->headers[$name] = $value;
        }

        return $new;
    }

    /**
     * @phan-suppress PhanParamSignatureRealMismatchReturnType
     */
    public function withoutHeader(string $name): static
    {
        if ($h = $this->getHeaderByName($name)) {
            $new = clone $this;
            unset($new->headers[$h]);

            return $new;
        }

        return $this;
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

    private function getHeaderByName(string $name): ?string
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('Header name is empty string');
        }

        return ($h = \preg_grep('/^'.\preg_quote($name, '').'$/i', \array_keys($this->headers)))
            ? $h[0]
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
