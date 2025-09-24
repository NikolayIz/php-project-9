PORT ?= 8000

start:
	php -S 0.0.0.0:$(PORT) -t public public/index.php

setup:
	composer install

validate:
	composer validate

lint:
	./vendor/bin/phpcs src public tests

lint-fix:
	./vendor/bin/phpcbf src public tests