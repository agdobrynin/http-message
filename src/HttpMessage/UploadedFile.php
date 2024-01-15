<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        // TODO: Implement getStream() method.
    }

    /**
     * @inheritDoc
     */
    public function moveTo(string $targetPath): void
    {
        // TODO: Implement moveTo() method.
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        // TODO: Implement getSize() method.
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        // TODO: Implement getError() method.
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): ?string
    {
        // TODO: Implement getClientFilename() method.
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): ?string
    {
        // TODO: Implement getClientMediaType() method.
    }
}
