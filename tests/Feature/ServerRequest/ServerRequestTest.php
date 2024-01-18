<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\UploadedFile;
use Kaspi\HttpMessage\Uri;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;

\describe('Methods of '.ServerRequest::class, function () {
    \it('getCookieParams, withCookieParams', function () {
        \expect(($sr = new ServerRequest())->getCookieParams())->toBe([])
            ->and(
                $sr2 = $sr->withCookieParams(['q' => 'post', ['x' => [1, 2]]])
            )->not->toBe($sr)
            ->and($sr2->getCookieParams())->toBe(['q' => 'post', ['x' => [1, 2]]])
        ;
    });

    \it('getQueryParams, withQueryParams', function () {
        \expect(($sr = new ServerRequest())->getQueryParams())->toBe([])
            ->and(
                $sr2 = $sr->withQueryParams(['q' => 'post', ['x' => [1, 2]]])
            )->not->toBe($sr)
            ->and($sr2->getQueryParams())->toBe(['q' => 'post', ['x' => [1, 2]]])
        ;
    });

    \it('getUploadedFiles, withUploadedFiles', function () {
        $file = vfsStream::newFile('note.txt')->withContent('Hello World!')->at(vfsStream::setup());
        $uploadedFile = new UploadedFile($file->url(), \UPLOAD_ERR_OK);

        \expect(($sr = new ServerRequest())->getUploadedFiles())->toBe([])
            ->and(
                $sr2 = $sr->withUploadedFiles([$uploadedFile])
            )->not->toBe($sr)
            ->and(\current($sr2->getUploadedFiles()))->toBe($uploadedFile)
            ->and(\current($sr2->getUploadedFiles())->getStream())->toBeInstanceOf(StreamInterface::class)
            ->and((string) \current($sr2->getUploadedFiles())->getStream())->toBe('Hello World!')
        ;
    });
})
    ->covers(ServerRequest::class, Message::class, Request::class, Stream::class, Uri::class, UploadedFile::class);
