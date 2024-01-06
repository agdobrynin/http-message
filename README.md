# HTTP message PSR-7 implementation

## Использование Docker образа с PHP 8.2

---

Собрать контейнер
```shell
docker-compose build
```
Установить зависимости php composer-а:
```shell
docker-compose run --rm php composer install
```
Прогнать тесты с отчетом о покрытии кода
```shell
docker-compose run --rm php vendor/bin/phpunit
```
⛑ pезультаты будут в папке `.coverage-html`

Можно работать в shell оболочке в docker контейнере:
```shell
docker-compose run --rm php sh
```
