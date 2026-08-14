DOCKER := docker

PHP_VERSIONS := 8.1 8.2 8.3 8.4 8.5 8.6
PHP_VERSION ?= 8.4

IMAGE := urllinker-php
TAG := $(IMAGE):$(PHP_VERSION)

DOCKERFILE_DIR := scripts/docker/php
WORKDIR := /app
DOCKER_RUN_ARGS := run --rm --user "$(shell id -u):$(shell id -g)" --env "HOME=/tmp" --volume "$(CURDIR):$(WORKDIR)" --workdir "$(WORKDIR)"

.PHONY: build build-all check-docker clean codestyle composer coverage install phpunit phpstan rector shell test test-all

.DEFAULT_GOAL := test

check-docker:
	@command -v docker >/dev/null 2>&1 || { echo "error: Docker is required for this target. Install Docker, then run the tools inside the dev container." >&2; exit 1; }

build: check-docker
	docker build --file "$(DOCKERFILE_DIR)/Dockerfile.$(PHP_VERSION)" --tag "$(TAG)" .

build-all: check-docker
	@for v in $(PHP_VERSIONS); do \
		echo "==> Building urllinker-php:$$v"; \
		$(MAKE) build PHP_VERSION=$$v || exit 1; \
	done

install: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer install

composer: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer $(ARGS)

shell: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" bash

phpunit: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer phpunit

phpstan: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer phpstan

codestyle: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer codestyle

rector: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" composer rector

coverage: build
	$(DOCKER) $(DOCKER_RUN_ARGS) --env "XDEBUG_MODE=coverage" "$(TAG)" composer coverage

test: build
	$(DOCKER) $(DOCKER_RUN_ARGS) "$(TAG)" sh -c "if [ ! -f vendor/autoload.php ]; then composer install; fi; composer test"

test-all: check-docker
	@for v in $(PHP_VERSIONS); do \
		echo "==> Testing PHP $$v"; \
		$(MAKE) composer PHP_VERSION=$$v ARGS="update" || exit 1; \
		$(MAKE) test PHP_VERSION=$$v || exit 1; \
	done
	@echo "All PHP versions passed."

clean:
	rm -rf build vendor .php-cs-fixer.cache .phpunit.result.cache
