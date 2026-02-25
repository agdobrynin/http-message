<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage\UploadedFile;

use Generator;
use InvalidArgumentException;
use Kaspi\HttpMessage\CreateStreamFromStringTrait;
use Kaspi\HttpMessage\Stream;
use Kaspi\HttpMessage\Stream\FileStream;
use Kaspi\HttpMessage\UploadedFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Kaspi\HttpMessage\StreamAdapter;

use function file_get_contents;
use function fileperms;
use function filesize;
use function sprintf;
use function substr;

use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_OK;

/**
 * @internal
 */
#[CoversClass(UploadedFile::class)]
#[CoversClass(Stream::class)]
#[CoversClass(FileStream::class)]
#[CoversClass(CreateStreamFromStringTrait::class)]
class UploadedFileTest extends TestCase
{
    public function testEmptyStreamOrFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter. "fileOrStream"');

        new UploadedFile('', 0);
    }

    #[DataProvider('wrongErrorCodeProvider')]
    public function testWrongErrorCode(int $error): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid upload file error');

        new UploadedFile('file.txt', $error);
    }

    public static function wrongErrorCodeProvider(): Generator
    {
        yield 'negative value' => [-1];

        yield 'error code' => [5];

        yield 'error code 9' => [9];

        yield 'error code 10' => [10];

        yield 'error code 100' => [100];
    }

    #[DataProvider('supportErrorCodeProvider')]
    public function testSupportErrorCode(int $error): void
    {
        $u = new UploadedFile('file.txt', $error);

        self::assertEquals($error, $u->getError());
    }

    public static function supportErrorCodeProvider(): Generator
    {
        yield 'set #0' => [UPLOAD_ERR_OK];

        yield 'set #1' => [UPLOAD_ERR_INI_SIZE];

        yield 'set #2' => [UPLOAD_ERR_FORM_SIZE];

        yield 'set #3' => [UPLOAD_ERR_PARTIAL];

        yield 'set #4' => [UPLOAD_ERR_NO_FILE];

        yield 'set #6' => [UPLOAD_ERR_NO_TMP_DIR];

        yield 'set #7' => [UPLOAD_ERR_CANT_WRITE];

        yield 'set #8' => [UPLOAD_ERR_EXTENSION];
    }

    public function testGetStreamFromFile(): void
    {
        $fSize = filesize(__FILE__);
        $stream = (new UploadedFile(__FILE__, UPLOAD_ERR_OK))->getStream();

        self::assertEquals(__FILE__, $stream->getMetadata('uri'));
        self::assertEquals($fSize, $stream->getSize());
    }

    public function testGetStreamFromNonExistFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No such file or directory');

        $uploadedFile = new UploadedFile('/tmp/x.jpg', UPLOAD_ERR_OK);
        $uploadedFile->getStream();
    }

    public function testGetStreamFromFileWithoutPermission(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fopen(vfs://root/tmpAbc): Failed');

        $root = vfsStream::setup();
        // file with permission write only.
        $file = vfsStream::newFile('tmpAbc', 0200)->at($root);
        $uploadedFile = new UploadedFile($file->url(), UPLOAD_ERR_OK);

        $permission = substr(sprintf('%o', fileperms($root->getChild('tmpAbc')->url())), -4);

        self::assertEquals('0200', $permission);

        // Fire exception
        $uploadedFile->getStream();
    }

    public function testGetStreamFromStream(): void
    {
        $stream = StreamAdapter::make('');
        $uploadedFile = (new UploadedFile($stream, UPLOAD_ERR_OK));

        self::assertSame($stream, $uploadedFile->getStream());
    }

    public function testGetStreamFromFileWithErr(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Uploaded file has error code: '.UPLOAD_ERR_FORM_SIZE);

        (new UploadedFile('/tmp/file', UPLOAD_ERR_FORM_SIZE))->getStream();
    }

    public function testDefaultValues(): void
    {
        $uploadedFile = new UploadedFile('x.tmp', UPLOAD_ERR_OK);

        self::assertNull($uploadedFile->getSize());
        self::assertNull($uploadedFile->getClientFilename());
        self::assertNull($uploadedFile->getClientMediaType());
        self::assertEquals(0, $uploadedFile->getError());
    }

    public function testFileWithParams(): void
    {
        $uploadedFile = new UploadedFile('x.tmp', UPLOAD_ERR_OK, 200, 'img.png', 'image/png');

        self::assertEquals(200, $uploadedFile->getSize());
        self::assertEquals('img.png', $uploadedFile->getClientFilename());
        self::assertEquals('image/png', $uploadedFile->getClientMediaType());
        self::assertEquals(0, $uploadedFile->getError());
    }

    public function testMoveToWithErrorCode(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Uploaded file has error code: '.UPLOAD_ERR_FORM_SIZE);

        (new UploadedFile('x.x', UPLOAD_ERR_FORM_SIZE))->moveTo('/tmp.tmp');
    }

    public function testMoveToEmptyTargetPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target path must be provide non-empty string');

        (new UploadedFile('x.x', UPLOAD_ERR_OK))->moveTo('');
    }

    public function testMoveToTargetPathPermissionDenied(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permission denied');

        $root = vfsStream::setup(structure: [
            'tmpAbc' => 'aaaa',
            'store' => [],
        ]);
        $root->getChild('store')->chmod(0400);

        (new UploadedFile($root->getChild('tmpAbc')->url(), UPLOAD_ERR_OK))
            ->moveTo($root->getChild('store')->url().'/file.txt')
        ;
    }

    public function testMoveToWhenUploadedFilePermissionDeny(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permission denied');

        $root = vfsStream::setup(structure: [
            'tmp.file' => 'foo bar',
        ]);
        vfsStream::newDirectory('store', 0444)->at($root);

        $uf = new UploadedFile(vfsStream::url('root/tmp.file'), UPLOAD_ERR_OK);

        $uf->moveTo(vfsStream::url('root/store/file.txt'));
    }

    public function testMoveToTwice(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file has already been moved');

        vfsStream::setup(structure: [
            'uploaded.file' => 'hello world',
            'store' => [],
        ]);

        $uploadedFiles = new UploadedFile(vfsStream::url('root/uploaded.file'), UPLOAD_ERR_OK);
        $uploadedFiles->moveTo(vfsStream::url('root/store/file.txt'));

        self::assertEquals('hello world', file_get_contents(vfsStream::url('root/store/file.txt')));

        // ⛔ moved file try move again will be fire exception
        $uploadedFiles->moveTo(vfsStream::url('root/store/file_new.txt'));
    }
}
