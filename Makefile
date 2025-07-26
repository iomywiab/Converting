SHELL := bash
.DEFAULT_GOAL := help
OS := $(shell uname)
DEFAULT_CONTAINER := php-8.1-debug
#PROJECT_POSTFIX=iomywiab-enums

.PHONY: help
help: # print documentation from comments: https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
	@egrep -h '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort -n | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-24s\033[0m %s\n", $$1, $$2}'

.PHONY: init-dirs
init-dirs: ## create known directories
	mkdir -p ./config
	mkdir -p ./docs
	mkdir -p ./public
	mkdir -p ./src
	mkdir -p ./tests/Examples
	mkdir -p ./tests/Fixtures
	mkdir -p ./tests/Integration
	mkdir -p ./tests/Load
	mkdir -p ./tests/Security
	mkdir -p ./tests/Unit
	mkdir -p ./tmp/phpstan
	mkdir -p ./tmp/phpstorm/coverage
	mkdir -p ./tmp/phpunit
	mkdir -p ./tmp/xdebug/profiler
	ls -lha ./tests

.PHONY: shell
shell: ## run bash in Docker container "$(DEFAULT_CONTAINER)"
	docker compose exec "$(DEFAULT_CONTAINER)" bash

.PHONY: stan
stan: ## run PHPStan in Docker container "phpstan"
	docker compose exec phpstan sh -c "php -d memory_limit=2G ./vendor/bin/phpstan --configuration=./config/phpstan.neon analyse"

.PHONY: up
up: ## start containers (composer, php, phpstan)
	docker compose up -d composer &
	docker compose up -d "$(DEFAULT_CONTAINER)" &
	docker compose up -d phpstan &

.PHONY: down
down: ## stop containers (composer, php, phpstan)
	docker compose rm -f -s "composer" &
	docker compose rm -f -s "$(DEFAULT_CONTAINER)" &
	docker compose rm -f -s "phpstan"

.PHONY: vendor
vendor: ## update the vendor directory
	docker compose exec "$(DEFAULT_CONTAINER)" sh -c "composer update --no-interaction"
