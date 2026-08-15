## Agent skills

### Issue tracker

Issues live in this repo's GitHub Issues, operated via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

Default labels: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.

### Releasing

Releases are developer-initiated, never agent-launched. Prepare up to the PR and stop there. See `docs/agents/releasing.md`.

## Development environment

PHP runs **only** inside the Docker dev container — never invoke `php`, `composer`, or `vendor/bin/*` on the host. Docker is required for `make qa`. Use the Makefile:

- `make qa` — full check (phpunit + phpstan + codestyle); installs dependencies on first run
- `make install` / `make composer ARGS="install"` — install Composer dependencies
- `make phpunit` / `make phpstan` / `make codestyle` / `make rector` / `make coverage`
- `make shell` — interactive shell inside the container

The dev container runs the lowest supported PHP version (PHP 8.2); the cross-version matrix (8.2–8.6, with 8.6 still un-released and running as a CI-only canary) runs in GitHub Actions. See `docs/dev-environment.md` for the full reference.
