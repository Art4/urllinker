## Agent skills

### Issue tracker

Issues live in this repo's GitHub Issues, operated via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

Default labels: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.

## Development environment

PHP runs **only** inside the Docker dev container — never invoke `php`, `composer`, or `vendor/bin/*` on the host. Docker is required for `make test`. Use the Makefile:

- `make test` — full check (phpunit + phpstan + codestyle); installs dependencies on first run
- `make install` / `make composer ARGS="install"` — install Composer dependencies
- `make phpunit` / `make phpstan` / `make codestyle` / `make rector` / `make coverage`
- `make shell` — interactive shell inside the container
- `make test-all` — run the full check on every supported PHP version
- Set `PHP_VERSION=8.x` to use a different PHP version (default `8.4`)

See `docs/dev-environment.md` for the full reference.
