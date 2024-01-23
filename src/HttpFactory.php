<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\Stream\PhpTempStream;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function func_num_args;

use const UPLOAD_ERR_OK;

/**
 * PSR-17 Factory.
 */
class HttpFactory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UploadedFileFactoryInterface, UriFactoryInterface
{
    use CreateStreamFromStringTrait;

    public function createRequest(string $method, $uri): RequestInterface
    {
        if (!isset($this->streamResolver)) {
            $this->streamResolver = fn () => new PhpTempStream();
        }

        return new Request(method: $method, uri: $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (!isset($this->streamResolver)) {
            $this->streamResolver = fn () => new PhpTempStream();
        }

        return new Response(code: $code, reasonPhrase: func_num_args() >= 2 ? $reasonPhrase : null);
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (!isset($this->streamResolver)) {
            $this->streamResolver = fn () => new PhpTempStream();
        }

        return new ServerRequest(method: $method, uri: $uri, serverParams: $serverParams);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        if (!isset($this->streamResolver)) {
            $this->streamResolver = fn () => new PhpTempStream();
        }

        return $this->streamFromString($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'rb'): StreamInterface
    {
        return new FileStream($filename, $mode);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        return new UploadedFile(
            streamOrFile: $stream,
            error: $error,
            size: $size ?? $stream->getSize(),
            originFileName: $clientFilename,
            originMediaType: $clientMediaType
        );
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
