<?php

declare(strict_types=1);

use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\UploadedFile;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Kaspi\HttpMessage\StreamAdapter;

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
            \expect(new UploadedFile(StreamAdapter::make(''), 0))
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
            ->throws(RuntimeException::class, 'No such file or directory')
        ;

        \it('from file without permission', function () {
            $root = vfsStream::setup();
            // file with permission write only.
            $file = vfsStream::newFile('tmpAbc', 0200)->at($root);
            $uploadedFile = new UploadedFile($file->url(), \UPLOAD_ERR_OK);

            $permission = \substr(\sprintf('%o', \fileperms($root->getChild('tmpAbc')->url())), -4);
            \expect($permission)->toBe('0200');

            \set_error_handler(static fn () => false);
            $uploadedFile->getStream();
        })
            ->throws(RuntimeException::class, 'fopen(vfs://root/tmpAbc): Failed')
        ;

        \it('from stream', function () {
            $stream = StreamAdapter::make('');
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
    })
        ->covers(CreateStreamFromStringTrait::class, FileStream::class)
    ;

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

        \it('target folder with permission deny', function () {
            $root = vfsStream::setup(structure: [
                'tmpAbc' => 'aaaa',
                'store' => [],
            ]);
            $root->getChild('store')->chmod(0400);

            \set_error_handler(static fn () => false);

            (new UploadedFile($root->getChild('tmpAbc')->url(), \UPLOAD_ERR_OK))
                ->moveTo($root->getChild('store')->url().'/file.txt')
            ;
        })
            ->throws(RuntimeException::class, 'Permission denied')
        ;

        \it('uploaded file permission deny', function () {
            $root = vfsStream::setup();
            $dir = vfsStream::newDirectory('store')->at($root);

            \set_error_handler(static fn () => false);

            (new UploadedFile($dir->url().'/lalala/tmp', \UPLOAD_ERR_OK))
                ->moveTo($dir->url().'/file.txt')
            ;
        })
            ->throws(RuntimeException::class, 'Cannot move uploaded file')
        ;

        \it('uploaded file moved success and try move file again', function () {
            $root = vfsStream::setup(structure: [
                'uploaded.file' => 'hello world',
                'store' => [],
            ]);
            \set_error_handler(static fn () => false);

            $uploadedFiles = new UploadedFile($root->getChild('uploaded.file')->url(), \UPLOAD_ERR_OK);
            $uploadedFiles->moveTo($root->getChild('store')->url().'/file.txt');

            \expect($root->hasChild('store/file.txt'))->toBeTrue()
                ->and($root->getChild('store/file.txt')?->getContent())->toBe('hello world')
            ;

            // ⛔ moved file try move again will be fire exception
            $uploadedFiles->moveTo($root->getChild('store')->url().'/file.txt');
        })
            ->throws(RuntimeException::class, 'The uploaded file has already been moved')
        ;

        \describe('from stream to target with exception', function () {
            \beforeEach(function () {
                $this->root = vfsStream::setup();
                $this->uploadedStream = new Stream(\fopen(vfsStream::newFile('uploadFile.ttt')
                    ->withContent('hello world')->at($this->root)->url(), 'rb'));
            });

            \it('from stream to target permission', function ($targetPath) {
                $uploadedFiles = new UploadedFile($this->uploadedStream, \UPLOAD_ERR_OK);

                \set_error_handler(static fn () => false);
                $uploadedFiles->moveTo($targetPath);
            })
                ->with([
                    'target is directory' => [
                        'targetPath' => fn () => vfsStream::newDirectory('store')->at($this->root)->url(),
                    ],
                    'target file is permission deny' => [
                        'targetPath' => fn () => vfsStream::newFile('my.txt', 0444)->at($this->root)->url(),
                    ],
                ])
                ->throws(RuntimeException::class, 'Cannot create stream from')
            ;
        });

        \it('from stream to stream', function () {
            $root = vfsStream::setup();
            $dir = vfsStream::newDirectory('store')->at($root);
            $file = vfsStream::newFile('randname')
                ->withContent(LargeFileContent::withMegabytes(2))->at($root)
            ;
            $uploadedFiles = new UploadedFile(new Stream(\fopen($file->url(), 'rb')), \UPLOAD_ERR_OK);
            $uploadedFiles->moveTo($dir->url().'/file.txt');

            // 2 Mb = 2097152 bytes
            \expect($dir->getChild('file.txt')?->size())->toBe(2097152);

            // ⛔ moved file try move again will be fire exception
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('The uploaded file has already been moved');

            $uploadedFiles->moveTo($dir->url().'/file.txt');
        });

        \it('from stream to stream with disk quota', function () {
            $root = vfsStream::setup();
            $dir = vfsStream::newDirectory('store')->at($root);
            $file = vfsStream::newFile('uploaded_file.tmp')
                ->withContent(LargeFileContent::withKilobytes(2))->at($root)
            ;
            vfsStream::setQuota(4000);

            (new UploadedFile(new Stream(\fopen($file->url(), 'rb')), \UPLOAD_ERR_OK))
                ->moveTo($dir->url().'/file.txt')
            ;
        })
            ->throws(RuntimeException::class, 'Cannot copy from')
        ;
    });
})
    ->covers(UploadedFile::class)
;
