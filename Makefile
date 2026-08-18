.PHONY: build up down install test

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

install:
	docker-compose run --rm app composer install

update:
	docker-compose run --rm app composer update

test:
	docker-compose run --rm app vendor/bin/phpunit
