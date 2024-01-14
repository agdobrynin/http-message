# HTTP message PSR-7 implementation

## Тесты
Прогнать тесты без отчёта покрытия кода в html
```shell
composer test
```
Запуск тестов с проверкой покрытия кода тестами отчётом в html формате
```shell
./vendor/bin/pest
```

## Статический анализ кода

Для статического анализа используем пакет [Phan](https://github.com/phan/phan).

Запуск без PHP расширения [PHP AST](https://github.com/nikic/php-ast)

```shell
./vendor/bin/phan --allow-polyfill-parser
```

## Code style
Для приведения кода к стандартам используем php-cs-fixer который объявлен
в dev зависимости composer-а

```shell
composer fixer
```

## Использование Docker образа с PHP 8.1, 8.2, 8.3

Указать образ с версией PHP можно в файле `.env` в ключе `PHP_IMAGE`.
По умолчанию контейнер собирается с образом `php:8.1-cli-alpine`.

Собрать контейнер
```shell
docker-compose build
```
Установить зависимости php composer-а:
```shell
docker-compose run --rm php composer install
```
Прогнать тесты с отчетом о покрытии кода и отчётом в html формате
```shell
docker-compose run --rm php vendor/bin/pest --compact
```
⛑ pезультаты будут в папке `.coverage-html`

Статический анализ кода Phan (_static analyzer for PHP_)

```shell
docker-compose run --rm php vendor/bin/phan
```

Можно работать в shell оболочке в docker контейнере:
```shell
docker-compose run --rm php sh
```
###### Использование Makefile команд.
Проверить и исправить code style:
```shell
make fix
```
Запустить статический анализатор кода:
```shell
make stat
```
Запустить тесты:
```shell
make test
```
Запустить все стадии проверок:
```shell
make all
```
