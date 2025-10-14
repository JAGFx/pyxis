# -- Start Docker
start:
	@docker compose up -d

stop:
	@docker compose down --remove-orphans

restart: stop start
# -- End Docker

# -- Start Environment
build: stop
	@docker compose build --pull --no-cache

db: start
	@sleep 1s
	@bin/php bin/console d:d:d -f --if-exists
	@bin/php bin/console d:d:c
	@bin/php bin/console d:m:m -n

db\:test: start
	@sleep 1s
	@bin/php bin/console d:d:d -f --if-exists --env=test
	@bin/php bin/console d:d:c --env=test
	@bin/php bin/console d:m:m -n --env=test
	@bin/php bin/console h:f:l -n --purge-with-truncate --env=test

install: start db
	@sleep 1s
	@bin/php composer install
	@npm i

fixture: db
	@bin/php bin/console h:f:l -n --purge-with-truncate

init: build
	@echo "APP_ENV=dev" > .env.local
	@$(MAKE) install
	@$(MAKE) fixture
	@docker network create -d bridge otel-network-bridge

# -- End Environment

# -- Start Code linter & test (CI)
test: db\:test test\:unit test\:integration

test\:unit:
	@bin/php bin/phpunit tests/Unit

test\:integration:
	@bin/php bin/phpunit tests/Integration

lint:
	bin/php php-cs-fixer fix --using-cache=no --diff
	bin/php vendor/bin/phpstan analyse -n src
	bin/php vendor/bin/rector process src

ci: lint test

# -- End Code linter & test (CI)
