tests:
	docker-compose run --rm php vendor/bin/phpspec run
	docker-compose run --rm php vendor/bin/php-cs-fixer fix --config=.php_cs.php --dry-run src
	docker-compose run --rm php vendor/bin/php-cs-fixer fix --config=.php_cs.php --dry-run spec
	docker-compose run --rm php vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1
