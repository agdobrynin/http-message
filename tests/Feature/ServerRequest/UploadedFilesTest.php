<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Message;
use Kaspi\HttpMessage\Request;
use Kaspi\HttpMessage\ServerRequest;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\UploadedFile;
use Kaspi\HttpMessage\Uri;

\describe('UploadedFiles for '.ServerRequest::class, function () {
    \it('getUploadedFiles, withUploadedFiles', function ($files) {
        \expect(($sr = new ServerRequest())->getUploadedFiles())->toBe([])
            ->and(
                $sr2 = $sr->withUploadedFiles($files)
            )->not->toBe($sr)
            ->and($sr2->getUploadedFiles())->toBe($files)
        ;
    })->with([
        'one level' => [
            [
                new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
            ],
        ],
        'multi levels' => [
            [
                'avatars' => [
                    new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                    new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                    'notes' => [
                        new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                        new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                    ],
                ],
            ],
        ],
    ]);

    \it('fail withUploadedFiles', function ($files) {
        (new ServerRequest())->withUploadedFiles($files);
    })
        ->with([
            'one level' => [
                [
                    new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                    '/tmp/my_file.jpg',
                ],
            ],
            'many levels' => [
                [
                    'avatars' => [
                        new UploadedFile(new Stream(\tmpfile()), \UPLOAD_ERR_OK),
                        'note' => [
                            '/tmp/my_file.jpg',
                        ],
                    ],
                ],
            ],
        ])
        ->throws(InvalidArgumentException::class, 'Items must be instance')
    ;
})
    ->covers(
        ServerRequest::class,
        UploadedFile::class,
        Message::class,
        Request::class,
        Stream::class,
        Uri::class,
        FileStream::class,
    )
;
