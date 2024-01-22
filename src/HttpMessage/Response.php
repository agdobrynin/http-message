<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
    use CreateResourceFromStringTrait;

    private const PHRASE = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authorative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        429 => 'Too Many Requests',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        511 => 'Network Authentication Required',
    ];

    private string $reasonPhrase;

    public function __construct(
        private int $code = 200,
        ?string $reasonPhrase = null,
        StreamInterface|string $body = '',
        array $headers = [],
        string $protocolVersion = '1.1'
    ) {
        $body = \is_string($body)
            ? new Stream(self::resourceFromString($body))
            : $body;
        parent::__construct($body, $headers, $protocolVersion);
        $this->checkStatusCode($this->code);

        if (null === $reasonPhrase) {
            $this->reasonPhrase = self::PHRASE[$this->code] ?? '';
        } else {
            $this->reasonPhrase = $this->reasonPhraseNormalize($reasonPhrase);
        }
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $this->checkStatusCode($code);
        $new = clone $this;
        $new->code = $code;
        $new->reasonPhrase = '' !== $reasonPhrase
            ? $this->reasonPhraseNormalize($reasonPhrase)
            : (self::PHRASE[$code] ?? '');

        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private function checkStatusCode(int $code): void
    {
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException('Invalid status code. Got: '.$code);
        }
    }

    private function reasonPhraseNormalize(string $reasonPhrase): string
    {
        if (1 !== \preg_match(self::RFC7230_FIELD_TOKEN, $reasonPhrase)) {
            $val = \var_export($reasonPhrase, true);

            throw new \InvalidArgumentException('Reason phrase must be RFC 7230 compatible. Got: '.$val);
        }

        return \trim($reasonPhrase, " \t");
    }
}
