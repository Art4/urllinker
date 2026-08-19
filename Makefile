DOCKER := docker

PHP_VERSION := 8.2

IMAGE := urllinker-php
TAG := $(IMAGE):$(PHP_VERSION)

DOCKERFILE_DIR := scripts/docker/php
WORKDIR := /app
DOCKER_RUN_ARGS := run --rm --user "$(shell id -u):$(shell id -g)" --env "HOME=/tmp" --volume "$(CURDIR):$(WORKDIR)" --workdir "$(WORKDIR)"

.PHONY: build check-docker clean codestyle composer coverage coverage-check install phpunit phpstan qa rector shell

.DEFAULT_GOAL := qa

check-docker:
	@command -v docker >/dev/null 2>&1 || { echo "error: Docker is required for this target. Install Docker, then run the tools inside the dev container." >&2; exit 1; }

build: check-docker
	docker build --file "$(DOCKERFILE_DIR)/Dockerfile" --tag "$(TAG)" .

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

coverage-check: build
	$(DOCKER) $(DOCKER_RUN_ARGS) --env "XDEBUG_MODE=coverage" "$(TAG)" composer coverage-check

qa: build
	$(DOCKER) $(DOCKER_RUN_ARGS) --env "XDEBUG_MODE=coverage" "$(TAG)" sh -c "if [ ! -f vendor/autoload.php ]; then composer install; fi; composer qa"

clean:
	rm -rf build vendor .php-cs-fixer.cache .phpunit.result.cache
