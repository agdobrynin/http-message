<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

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

/**
 * PSR-17 Factory.
 */
class HttpFactory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UploadedFileFactoryInterface, UriFactoryInterface
{
    use CreateResourceFromStringTrait;

    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request(method: $method, uri: $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response(code: $code, reasonPhrase: \func_num_args() >= 2 ? $reasonPhrase : null);
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest(method: $method, uri: $uri, serverParams: $serverParams);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return new Stream(resource: self::resourceFromString($content));
    }

    public function createStreamFromFile(string $filename, string $mode = 'rb'): StreamInterface
    {
        try {
            return ($r = @\fopen($filename, $mode)) !== false
                ? new Stream($r)
                : throw new \RuntimeException(\error_get_last()['message'] ?? '');
        } catch (\Throwable $error) {
            throw new \RuntimeException("Cannot stream from {$filename} [{$error}]");
        }
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
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
