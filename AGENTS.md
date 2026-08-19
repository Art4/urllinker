## Agent skills

### Issue tracker

Issues live in this repo's GitHub Issues, operated via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

Default labels: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Milestones

Every open issue and PR belongs to a milestone. See `docs/agents/milestones.md`.

### Domain docs

Single-context: `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.

### Releasing

Releases are developer-initiated, never agent-launched. Prepare up to the PR and stop there. See `docs/agents/releasing.md`.

### Housekeeping

Recurring maintenance is tracked in the open `housekeeping`-labelled GitHub issue (milestone: Later). See `docs/agents/housekeeping.md`. During any work, when you notice something housekeeping-worthy (a small refactoring note, outdated dependency, stale doc, etc.), **suggest** to the user that it be added to that issue; never add or edit it on your own.

## Git workflow

Never commit on `main`. Always create a new feature branch for the work and commit there; leave `main` untouched.

## Development environment

PHP runs **only** inside the Docker dev container — never invoke `php`, `composer`, or `vendor/bin/*` on the host. Docker is required for `make qa`. Use the Makefile:

- `make qa` — full check (phpunit + phpstan + codestyle + 100% code coverage gate); installs dependencies on first run
- `make install` / `make composer ARGS="install"` — install Composer dependencies
- `make phpunit` / `make phpstan` / `make codestyle` / `make rector` / `make coverage`
- `make shell` — interactive shell inside the container

The dev container runs the lowest supported PHP version (PHP 8.2); the cross-version matrix (8.2–8.6, with 8.6 still un-released and running as a CI-only canary) runs in GitHub Actions. See `docs/dev-environment.md` for the full reference.
