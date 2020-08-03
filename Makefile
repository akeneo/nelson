DOCKER_RUN = docker-compose run --rm php

.PHONY: fix-cs
fix-cs:
	$(DOCKER_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php

.PHONY: vendor
vendor:
	rm composer.lock
	$(DOCKER_RUN) composer validate --no-check-all
	$(DOCKER_RUN) php -d memory_limit=4G /usr/local/bin/composer install

tests:
	$(DOCKER_RUN) vendor/bin/phpspec run
