SHELL := /bin/sh

test:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/pest --compact

stat:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/phan

fix:
	@docker-compose -f docker-compose.yml run --rm php composer fix

install:
	@docker-compose -f docker-compose.yml run --rm php composer i

.PHONY: all
all:
	@docker-compose -f docker-compose.yml run --rm php sh -c "vendor/bin/php-cs-fixer fix && vendor/bin/phan && vendor/bin/pest --compact"

.PHONY: test-supports-php
test-supports-php:
	@docker-compose build --build-arg PHP_IMAGE=php:8.1-cli-alpine
	@docker-compose -f docker-compose.yml run --rm php sh -c "rm -rf vendor && composer install --no-progress && vendor/bin/pest --compact"

	@docker-compose build --build-arg PHP_IMAGE=php:8.2-cli-alpine
	@docker-compose -f docker-compose.yml run --rm php sh -c "rm -rf vendor && composer install --no-progress && vendor/bin/pest --compact"

	@docker-compose build --build-arg PHP_IMAGE=php:8.3-cli-alpine
	@docker-compose -f docker-compose.yml run --rm php sh -c "rm -rf vendor && composer install --no-progress && vendor/bin/pest --compact"

	@docker-compose build --build-arg PHP_IMAGE=php:8.4-cli-alpine
	@docker-compose -f docker-compose.yml run --rm php sh -c "rm -rf vendor && composer install --no-progress && vendor/bin/pest --compact"

	@docker-compose build #build container defined in .env file as PHP_IMAGE
	@docker-compose -f docker-compose.yml run --rm php sh -c "rm -rf vendor && composer install --no-progress"
