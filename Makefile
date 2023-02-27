PORT ?= 8000

start: migrate start-server

start-server:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

install:
	composer install

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public src

migrate:
	php bin/console migrate
