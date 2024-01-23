# 🕸 HTTP message PSR-7 implementation.

Kaspi/http-message is a lightweight implementation 
[PSR-7](https://www.php-fig.org/psr/psr-7), 
[PSR-17](https://www.php-fig.org/psr/psr-17) for >= PHP 8.1

Implementation of PSR-17:

- `\Kaspi\HttpMessage\HttpFactory::class`

Implementation of PSR-7:

- `\Kaspi\HttpMessage\Message::class`
- `\Kaspi\HttpMessage\Request::class`
- `\Kaspi\HttpMessage\Response::class`
- `\Kaspi\HttpMessage\ServerRequest::class`
- `\Kaspi\HttpMessage\Stream::class`
- `\Kaspi\HttpMessage\UploadedFile::class`
- `\Kaspi\HttpMessage\Uri::class`

Additional implementations for `\Psr\Http\Message\StreamInterface`

- `\Kaspi\HttpMessage\Stream\FileStream::class`
- `\Kaspi\HttpMessage\Stream\PhpMemoryStream::class`
- `\Kaspi\HttpMessage\Stream\PhpTempStream::class`

## Installation

```shell
composer require kaspi/http-message
```

- [Local development](#local-development) (without Docker)
- [With Docker images](#using-docker-image-with-php-81-82-83) (WSL, Linux) 

## Local development

Required PHP 8.1, php Composer

### Testing
Run test without code coverage
```shell
composer test
```
Running tests with checking code coverage by tests with a report in html format
```shell
./vendor/bin/pest --compact
```
Requires installed [PCOV](https://github.com/krakjoe/pcov) driver

_⛑ the results will be in the folder `.coverage-html`_

### Static code analysis

For static analysis we use the package [Phan](https://github.com/phan/phan).

Running without PHP extension [PHP AST](https://github.com/nikic/php-ast)

```shell
./vendor/bin/phan --allow-polyfill-parser
```

### Code style
To bring the code to standards, we use php-cs-fixer which is declared
in composer's dev dependencies

```shell
composer fixer
```

## Using Docker image with PHP 8.1, 8.2, 8.3

You can specify the image with the PHP version in the `.env` file in the `PHP_IMAGE` key.
By default, the container is built with the `php:8.1-cli-alpine` image.

Build docker container
```shell
docker-compose build
```
Install php composer dependencies:
```shell
docker-compose run --rm php composer install
```
Run tests with a code coverage report and a report in html format
```shell
docker-compose run --rm php vendor/bin/pest --compact
```
⛑ the results will be in the folder `.coverage-html`

Статический анализ кода Phan (_static analyzer for PHP_)

```shell
docker-compose run --rm php vendor/bin/phan
```

You can work in a shell in a docker container:
```shell
docker-compose run --rm php sh
```
##### Using Makefile commands.
Check and correct code style:
```shell
make fix
```
Run the static code analyzer:
```shell
make stat
```
Run tests:
```shell
make test
```
Run all stages of checks:
```shell
make all
```
