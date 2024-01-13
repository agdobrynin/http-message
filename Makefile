SHELL := /bin/sh

test:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/phpunit

stat:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/phan

