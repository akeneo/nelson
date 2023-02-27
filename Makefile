.PHONY: cs-fix
cs-fix:
	docker-compose run --rm php vendor/bin/php-cs-fixer --config=.php_cs.php fix src
	docker-compose run --rm php vendor/bin/php-cs-fixer --config=.php_cs.php fix tests

.PHONY: all-tests
all-tests:
	docker-compose run --rm php vendor/bin/php-cs-fixer --config=.php_cs.php fix src --dry-run --diff
        docker-compose run --rm php vendor/bin/php-cs-fixer --config=.php_cs.php fix tests --dry-run --diff
	docker-compose run --rm php vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1
	docker-compose run --rm php vendor/bin/phpspec run
	docker-compose run --rm php vendor/bin/simple-phpunit
