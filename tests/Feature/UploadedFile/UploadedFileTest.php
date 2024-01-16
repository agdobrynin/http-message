<?php

declare(strict_types=1);

use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

\describe('Tests for '.UploadedFile::class, function () {
    \describe('Constructor', function () {
        \describe('has throws', function () {
            \it('empty streamOrFile', function () {
                new UploadedFile('', 0);
            })
                ->throws(InvalidArgumentException::class, 'Invalid parameter. "fileOrStream"')
            ;

            \it('wrong error code', function (int $error) {
                new UploadedFile('file.txt', $error);
            })
                ->throws(InvalidArgumentException::class, 'Invalid upload file error')
                ->with([
                    'negative value' => -1,
                    'error code' => 5,
                    'error code 9' => 9,
                    'error code 10' => 10,
                    'error code 100' => 100,
                ])
            ;
        })
            ->covers(UploadedFile::class)
        ;

        \it('available StreamInterface', function () {
            \expect(new UploadedFile(new Stream(''), 0))
                ->toBeInstanceOf(UploadedFileInterface::class)
            ;
        })
            ->coversClass(Stream::class)
        ;

        \it('Support UPLOAD_ERR code', function (int $error) {
            \expect(new UploadedFile('file.txt', $error))
                ->toBeInstanceOf(UploadedFileInterface::class)
            ;
        })->with([
            0, 1, 2, 3, 4, 6, 7, 8,
        ]);
    });

    \describe('method getStream', function () {
        \it('from file', function () {
            $fSize = \filesize(__FILE__);
            $stream = (new UploadedFile(__FILE__, \UPLOAD_ERR_OK))->getStream();
            \expect($stream)->toBeInstanceOf(StreamInterface::class)
                ->and($stream->getMetadata('uri'))->toBe(__FILE__)
                ->and($stream->getSize())->toBe($fSize)
            ;
        });

        \it('from non exist file', function () {
            $uploadedFile = new UploadedFile('/tmp/x.jpg', \UPLOAD_ERR_OK);
            \set_error_handler(static fn () => false);
            $uploadedFile->getStream();
        })
            ->throws(RuntimeException::class, 'Cannot open file /tmp/x.jpg')
        ;

        \it('from stream', function () {
            $stream = new Stream('');
            $uploadedFile = (new UploadedFile($stream, \UPLOAD_ERR_OK));
            \expect($uploadedFile->getStream())
                ->toBeInstanceOf(StreamInterface::class)
                ->and($uploadedFile->getStream())->toBe($stream)
            ;
        });

        \it('from file with UPLOAD_ERR', function () {
            (new UploadedFile('/tmp/file', \UPLOAD_ERR_FORM_SIZE))->getStream();
        })
            ->throws(RuntimeException::class, 'Uploaded file has error code: '.\UPLOAD_ERR_FORM_SIZE)
        ;
    });

    \describe('simple methods', function () {
        \it('method with null value', function () {
            $uploadedFile = new UploadedFile('x.tmp', \UPLOAD_ERR_OK);

            \expect($uploadedFile->getSize())->toBeNull()
                ->and($uploadedFile->getClientFilename())->toBeNull()
                ->and($uploadedFile->getClientMediaType())->toBeNull()
                ->and($uploadedFile->getError())->toBe(\UPLOAD_ERR_OK)
            ;
        });

        \it('method with non-null value', function () {
            $uploadedFile = new UploadedFile('x.tmp', \UPLOAD_ERR_OK, 200, 'img.png', 'image/png');

            \expect($uploadedFile->getSize())->toBe(200)
                ->and($uploadedFile->getClientFilename())->toBe('img.png')
                ->and($uploadedFile->getClientMediaType())->toBe('image/png')
                ->and($uploadedFile->getError())->toBe(\UPLOAD_ERR_OK)
            ;
        });
    });

    \describe('method moveTo', function () {
        \it('with error code', function () {
            (new UploadedFile('x.x', \UPLOAD_ERR_FORM_SIZE))->moveTo('/tmp.tmp');
        })
            ->throws(RuntimeException::class, 'Uploaded file has error code: '.\UPLOAD_ERR_FORM_SIZE)
        ;

        \it('Empty target path', function () {
            (new UploadedFile('x.x', \UPLOAD_ERR_OK))->moveTo('');
        })
            ->throws(InvalidArgumentException::class)
        ;
    });
})
    ->covers(UploadedFile::class)
;
