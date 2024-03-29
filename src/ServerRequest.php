<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

use function array_key_exists;
use function array_walk_recursive;
use function is_array;
use function is_object;

class ServerRequest extends Request implements ServerRequestInterface
{
    private array $cookies = [];
    private array $query = [];

    /**
     * @var UploadedFileInterface[]
     */
    private array $uploadedFiles = [];
    private null|array|object $parsedBody = null;
    private array $attributes = [];

    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '',
        ?StreamInterface $body = null,
        array $headers = [],
        string $protocolVersion = '1.1',
        private readonly array $serverParams = [],
    ) {
        parent::__construct($method, $uri, $body, $headers, $protocolVersion);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookies = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->query;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        // @see https://www.php-fig.org/psr/psr-7/#16-uploaded-files
        array_walk_recursive($uploadedFiles, static function ($item): void {
            if (!$item instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Items must be instance of '.UploadedFileInterface::class);
            }
        });
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        if (null !== $data && !is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException('Invalid body data. Data must null, array or object');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return array_key_exists($name, $this->attributes)
            ? $this->attributes[$name]
            : $default;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }
}
