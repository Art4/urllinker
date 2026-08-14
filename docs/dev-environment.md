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

installs the Composer dependencies into `vendor/`. The `make install` target builds the image first, so no separate `make build` is needed. `make qa` also auto-runs `composer install` if `vendor/` is missing, so the very first invocation can simply be:

```bash
make qa
```

## Make targets

| Target | Description |
| --- | --- |
| `make build` | Build the dev container image |
| `make install` | Install Composer dependencies |
| `make qa` | Run the full check: phpunit + phpstan + codestyle (docker required) |
| `make phpunit` | Run the PHPUnit test suite |
| `make phpstan` | Run PHPStan static analysis |
| `make codestyle` | Fix code style |
| `make rector` | Run Rector in dry-run mode |
| `make coverage` | Run PHPUnit with HTML coverage report into `build/code-coverage/` |
| `make shell` | Open an interactive shell inside the container |
| `make composer ARGS="..."` | Run an arbitrary Composer command, e.g. `make composer ARGS="update"` |
| `make clean` | Remove local build artifacts (`build/`, `vendor/`, caches) |

## PHP version

The dev container runs PHP `8.2`. The test suite uses PHPUnit 10+ attributes, so PHP `8.1` — which resolves to PHPUnit `9.6` — cannot run the full check locally; `8.2` is the lowest supported version whose resolved toolchain (PHPUnit 11) passes `make qa`. The cross-version matrix (`8.1`–`8.6`) is run by GitHub Actions in `ci.yml`; running other versions locally is not supported. Dropping end-of-life PHP versions and adding PHP 8.6 to the CI matrix is tracked in [issue #29](https://github.com/Art4/urllinker/issues/29).

## How it works

- The image is a pure PHP runtime: the repository is bind-mounted into the container at `/app`, so no app files are baked into the image (see `.dockerignore`).
- The image is built from the [official PHP Docker image](https://hub.docker.com/_/php/) and installs `mbstring`, `git`, `unzip`, and Composer. Xdebug is installed but disabled by default; `make coverage` enables it via `XDEBUG_MODE=coverage`.
- The container runs as your host user (`--user $(id -u):$(id -g)`), so files written into the workspace (`vendor/`, `build/`, caches) stay owned by you.

## Troubleshooting

- **"docker: command not found"** — Docker (and the `docker` CLI) is required for every `make` target that touches PHP; the targets fail with an explicit error.
- **Permission problems on `vendor/` or `build/`** — the container already maps to your host user; if you ever ran an older setup as root, remove the directories with `make clean` and reinstall.