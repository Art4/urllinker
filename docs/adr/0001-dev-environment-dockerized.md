# Dockerized Development Environment

Status: accepted

Previously, development required PHP and Composer installed on the host, and CI and local development ran PHP in different ways. We decided that **PHP only ever runs inside the Docker dev container**: one pipeline-ready image per supported PHP version (`8.1`–`8.6`) under `scripts/docker/php/`, a `make` wrapper as the single entry point, and Composer baked into the images — there is no host PHP at all. CI keeps running on GitHub Actions with `shivammathur/setup-php` and is deliberately **not** migrated to Docker, so the test matrix stays on the CI runner while the dev container serves contributors and agents.

## Considered options

- **Single Dockerfile with an `ARG PHP_VERSION` build arg** — less duplication, but each PHP version needs a slightly different base (8.6 is currently a beta tag); separate per-version Dockerfiles keep each version fully self-contained and pin its exact base tag.
- **`docker compose` with one service per PHP version** — rejected: a library has no long-running services, so compose adds configuration weight; the Makefile drives raw `docker build`/`docker run` instead.
- **Migrating CI to the Docker images** — considered, rejected: CI already covers the version matrix on host PHP, and keeping it unchanged minimizes risk to the review pipeline. A consequence is that a broken Dockerfile is only caught by whoever runs the Makefile, not by CI.

## Consequences

- Composer scripts in `composer.json` are plain tool invocations (they run inside the container) and are never executed on the host.
- `vendor/` is written into the workspace by the container running as the host user, so artifacts stay owned by the developer.
- PHP 8.6 is unreleased (2026-11-19); `Dockerfile.8.6` builds on `php:8.6.0beta1-cli` and must be flipped to the stable tag after release.
- Xdebug is installed but inert unless `XDEBUG_MODE=coverage` is set, which `make coverage` does.