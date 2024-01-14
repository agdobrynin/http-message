SHELL := /bin/sh

test:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/pest --compact

stat:
	@docker-compose -f docker-compose.yml run --rm php vendor/bin/phan

fix:
	@docker-compose -f docker-compose.yml run --rm php composer fix

.PHONY: all
all: fix stat test
