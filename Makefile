SHELL := bash
.DEFAULT_GOAL := help
OS := $(shell uname)
DEFAULT_CONTAINER := php-8.1-debug
#PROJECT_POSTFIX=iomywiab-enums
EXPECTED_VERSION := 1.1.0
VERSION_URL := https://raw.githubusercontent.com/iomywiab/ProjectTemplate/refs/heads/main/version.txt


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

.PHONY: check-version
check-version: ## check if there is an update available for this script
	@echo "Checking version from $(VERSION_URL)..."
	@actual_version=$$(curl -sSfL $(VERSION_URL)); \
	if [ "$$actual_version" = "$(EXPECTED_VERSION)" ]; then \
		echo "Script is up-to-date: $$actual_version"; \
	else \
		echo "Update is available! This script: $(EXPECTED_VERSION), Found: $$actual_version" >&2; \
		exit 1; \
	fi

.PHONY: cov
cov: cov-xml cov-badge ## create a coverage file (tmp/phpstorm/coverage/cobertura.xml) and create the badge

.PHONY: cov-xml
cov-xml: ## create a coverage file (tmp/phpstorm/coverage/cobertura.xml)
	docker compose exec "$(DEFAULT_CONTAINER)" sh -c "php -dxdebug.mode=coverage /opt/project/vendor/phpunit/phpunit/phpunit --coverage-cobertura /opt/phpstorm-coverage/cobertura.xml --configuration /opt/project/config/phpunit.xml /opt/project/tests"

.PHONY: cov-badge
cov-badge: ## create a coverage badge from existing coverage file (tmp/phpstorm/coverage/cobertura.xml)
	mkdir -p ./docs
	@COVERAGE_PERCENT=$$(xmllint --xpath 'string(/coverage/@line-rate)' tmp/phpstorm/coverage/cobertura.xml); \
	COVERAGE_INT=$$(echo "$$COVERAGE_PERCENT * 100" | bc | xargs printf "%.0f"); \
	COLOR=$$( \
		if [ $$COVERAGE_INT -ge 90 ]; then echo "brightgreen"; \
		elif [ $$COVERAGE_INT -ge 75 ]; then echo "yellow"; \
		elif [ $$COVERAGE_INT -ge 50 ]; then echo "orange"; \
		else echo "red"; fi \
	); \
	curl -s "https://img.shields.io/badge/Line_coverage-$$COVERAGE_INT%25-$$COLOR.svg" -o docs/coverage-badge.svg; \
	echo "✅ Badge stored as docs/coverage-badge.svg"
