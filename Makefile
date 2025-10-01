PORT ?= 8000

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

setup:
	composer install

validate:
	composer validate

lint:
	./vendor/bin/phpcs src public tests

lint-fix:
	./vendor/bin/phpcbf src public tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover build/logs/clover.xml