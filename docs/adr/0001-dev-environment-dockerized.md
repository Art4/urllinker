# Dockerized Development Environment

Status: accepted

PHP only ever runs inside the Docker dev container, never on the host. There is one dev image (`scripts/docker/php/Dockerfile`) pinned to PHP `8.2` and a `make` wrapper is the single entry point (`make qa`, `make phpunit`, `make phpstan`, `make shell`, ...); Composer is baked into the image. CI stays on GitHub Actions with `shivammathur/setup-php` and deliberately owns the cross-version matrix (`8.1`–`8.6`), so the dev container only tests a single PHP version.

## Considered options

- **One image per supported PHP version** — originally chosen, then rejected on review: the shared `vendor/` plus per-version dev-dependency floors forced per-version re-resolution and a `test-all` loop. Since CI already runs every version, the local container only needs a single guardrail version.
- **Pinning PHP `8.1` (the lowest supported)** — tried, then rejected: `8.1` resolves PHPUnit `9.6`, which lacks the attribute classes (`DataProvider`, `CoversClass`) the modern test suite uses, so the full check cannot pass locally. `8.2` is the lowest supported version whose resolved toolchain (PHPUnit 11) passes the whole check.
- **`docker compose` with one service per PHP version** — rejected: a library has no long-running services; the Makefile drives raw `docker build`/`docker run`.
- **Migrating CI to the Docker images** — considered, rejected: CI already covers the version matrix on host PHP, and keeping it unchanged minimizes risk to the review pipeline.

## Consequences

- `vendor/` matches PHP `8.2`; running other versions locally is not supported — use CI for that.
- Dropping EOL PHP versions (currently `8.1`) and adding PHP `8.6` to the CI matrix is tracked in issue #29.
- Composer scripts are plain tool invocations that run inside the container, never on the host.
- `vendor/` is written into the workspace by the container running as the host user, so artifacts stay owned by the developer.
- Xdebug is installed but inert unless `XDEBUG_MODE=coverage` is set, which `make coverage` does.
