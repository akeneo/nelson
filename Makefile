tests:
	docker-compose run --rm php vendor/bin/phpspec run
	docker-compose run --rm php vendor/bin/php-cs-fixer fix --config=.php_cs.php --dry-run src
	docker-compose run --rm php vendor/bin/php-cs-fixer fix --config=.php_cs.php --dry-run spec
