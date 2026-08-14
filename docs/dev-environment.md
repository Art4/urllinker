# Development Environment

All PHP tooling runs inside Docker. PHP itself is never installed on the host and must not be invoked there.

## Requirements

- Docker (with BuildKit support; `docker buildx` is included in recent Docker versions)
- `make`

There is no PHP, Composer, or `vendor/` setup on the host — everything happens inside the `urllinker-php` container.

## First run

```bash
make install
```

installs the Composer dependencies into `vendor/`. The `make install` target builds the image for the default PHP version (8.4) first, so no separate `make build` is needed. `make test` also auto-runs `composer install` if `vendor/` is missing, so the very first invocation can simply be:

```bash
make test
```

## Make targets

| Target | Description |
| --- | --- |
| `make build` | Build the image for the default PHP version |
| `make build-all` | Build the images for all supported PHP versions |
| `make install` | Install Composer dependencies |
| `make test` | Run the full check: phpunit + phpstan + codestyle (docker required) |
| `make phpunit` | Run the PHPUnit test suite |
| `make phpstan` | Run PHPStan static analysis |
| `make codestyle` | Fix code style |
| `make rector` | Run Rector in dry-run mode |
| `make coverage` | Run PHPUnit with HTML coverage report into `build/code-coverage/` |
| `make test-all` | Run `make test` on every supported PHP version |
| `make shell` | Open an interactive shell inside the container |
| `make composer ARGS="..."` | Run an arbitrary Composer command, e.g. `make composer ARGS="update"` |
| `make clean` | Remove local build artifacts (`build/`, `vendor/`, caches) |

## PHP versions

Supported PHP versions are `8.1` – `8.6`. Each has its own Dockerfile under `scripts/docker/php/Dockerfile.<version>`. The default is PHP `8.4`; switch with the `PHP_VERSION` variable:

```bash
make test PHP_VERSION=8.2
make phpunit PHP_VERSION=8.6
```

The Composer dev dependencies (e.g. PHPUnit) have different version floors per PHP version (PHPUnit 12 needs PHP 8.3+, 11 needs 8.2+, 9.6 works on 8.1). The `composer.lock` in the working tree is not committed, so `make install` honors whichever lock is present. To test a single non-default version, re-resolve the dependencies for that PHP version first:

```bash
make composer PHP_VERSION=8.1 ARGS="update"
make test PHP_VERSION=8.1
```

`make test-all` re-resolves dependencies inside each version's container before running the check; afterwards `vendor/` and `composer.lock` match the last version tested.

### PHP 8.6 (beta)

PHP 8.6 is not yet released (stable is due 2026-11-19). `scripts/docker/php/Dockerfile.8.6` therefore builds on the `php:8.6.0beta1-cli` image. Flip it to `php:8.6-cli` once the stable release is out.

## How it works

- The images are pure PHP runtimes: the repository is bind-mounted into the container at `/app`, so no app files are baked into the image (see `.dockerignore`).
- Images are built from the [official PHP Docker images](https://hub.docker.com/_/php/) and install `mbstring`, `git`, `unzip`, and Composer. Xdebug is installed but disabled by default; `make coverage` enables it via `XDEBUG_MODE=coverage`. On PHP versions where Xdebug does not compile yet (e.g. betas), coverage is unavailable.
- Containers run as your host user (`--user $(id -u):$(id -g)`), so files written into the workspace (`vendor/`, `build/`, caches) stay owned by you.

## Troubleshooting

- **"docker: command not found"** — Docker (and the `docker` CLI) is required for every `make` target that touches PHP; the targets fail with an explicit error.
- **Permission problems on `vendor/` or `build/`** — the container already maps to your host user; if you ever ran an older setup as root, remove the directories with `make clean` and reinstall.
- **Coverage fails with "Xdebug..." errors** — Xdebug could not be installed for that PHP version; run `make phpstan`/`make test` instead, coverage is only checked in CI on PHP 8.4.