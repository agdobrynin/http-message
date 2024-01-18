<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
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

    public function __construct(
        private int $code = 200,
        private ?string $reasonPhrase = null,
        mixed $body = '',
        array $headers = [],
        string $protocolVersion = '1.1'
    ) {
        parent::__construct($body, $headers, $protocolVersion);
        $this->checkStatusCode($this->code);

        if (null === $this->reasonPhrase) {
            $this->reasonPhrase = self::PHRASE[$this->code] ?? '';
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
            ? $reasonPhrase
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
}
