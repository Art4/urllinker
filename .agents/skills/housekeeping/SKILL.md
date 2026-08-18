---
name: housekeeping
description: Run the recurring housekeeping maintenance on this repo. User-invoked.
disable-model-invocation: true
---

# Housekeeping

Recurring maintenance run against the open `housekeeping`-labelled GitHub issue, initiated by the developer — regularly, quarterly plus before each release. One issue at a time; it rides the Later milestone. The run works the issue's task list and Collected tasks, records reports, creates the next issue as its final task, then opens one closing PR (`Closes #<n>`) whose merge closes the issue.

Full conventions live in `docs/agents/housekeeping.md`.

## Process

### 1. Select the issue

List open issues with the `housekeeping` label:

```bash
gh issue list --state open --label housekeeping --json number,title,createdAt,body
```

- **Exactly one**, not fully ticked → work it.
- **Exactly one, fully ticked** → the previous run is complete and its closing PR awaits merge. Report that and stop.
- **None** → ask the user for the issue number, or offer to create the first one from the template (`.github/ISSUE_TEMPLATE/housekeeping.md`).
- **Two or more** → present each with title, created date, and open-checkbox count; ask which to work.

_Done_: the run's target issue number is fixed.

### 2. Reconcile to the template

Read the issue body (`gh issue view <n> --comments`). If its Tasks or Reports sections differ from the current template, rewrite the body to match the template — preserving already-ticked state and Collected tasks — and record the reconcile as an issue comment.

_Done_: the issue body matches the template, with prior state preserved.

### 3. Dependency pass

Update Composer dependencies in the dev container (`make composer ARGS="update"`), then run `composer audit`. Record both in the issue's Reports section.

_Done_: dependencies updated and both reports recorded.

### 4. CI pass

Check every action version in `.github/workflows/ci.yml` (`actions/checkout`, `shivammathur/setup-php`, `ramsey/composer-install`, `codecov/codecov-action`) against the latest, and check for GitHub deprecation warnings. Bump where newer exists and record the changes in Reports.

_Done_: actions current and the report recorded.

### 5. PHP calendar pass

Check the PHP release/EOL calendar for each matrix version. Identify: canaries to promote to stable, new canaries to add, EOL versions eligible for dropping. Apply canary additions and promotions to stable autonomously — they widen the supported range and are never breaking. **Dropping** a PHP version (a supported-range reduction) requires human approval: propose it, never apply without it. Then verify supported-range integrity across composer.json's `php` constraint, the `ci.yml` matrix, the dev container floor (`Makefile`/`Dockerfile`), and the docs, fixing any drift. Record all of it in Reports.

_Done_: canaries added or promoted; any PHP drop approved or recorded for the user; the supported-range integrity check done; the report updated.

### 6. TLD refresh

Fetch `https://data.iana.org/TLD/tlds-alpha-by-domain.txt`, compare against `src/DomainStorage.php`, and regenerate the file if newer. Record the IANA version before → after in Reports.

_Done_: the list is current and the report recorded.

### 7. Tooling pass

Run `make rector` and `make codestyle` in the dev container to absorb new rules. Record rule and tooling changes in Reports.

_Done_: tooling absorbed and the report recorded.

### 8. Work the Collected tasks

Implement every `- [ ]` item in the issue's Collected tasks section. Invoking the skill authorizes implementing the task list and Collected tasks; no per-task approval is needed. Tick each item and reference it (commit/PR number). A task that proves too big for housekeeping is re-filed as its own regular issue and removed from the backlog.

_Done_: every item is ticked or re-filed.

### 9. Docs and changelog hygiene

Update `AGENTS.md`, `CONTEXT.md`, `docs/*`, and `CHANGELOG.md` (under `[Unreleased]`) to reflect the changes made.

_Done_: docs and changelog reflect the run.

### 10. Verify

Run `make qa` in the dev container.

_Done_: the full check passes (phpunit, phpstan, codestyle).

### 11. Create the next issue

Tick the final task. Create the next issue from the template body (frontmatter stripped; `gh issue create --template` only works interactively):

```bash
awk '/^---$/{c++; next} c>=2{print}' .github/ISSUE_TEMPLATE/housekeeping.md > /tmp/housekeeping-body.md
gh issue create --title "Housekeeping $(date +%Y-%m)" --body-file /tmp/housekeeping-body.md --label housekeeping --milestone "<Later milestone title>"
```

Substitute `<Later milestone title>` with the current Later milestone title (read from `docs/agents/milestones.md`). Copy any unfinished Collected tasks into the new issue's Collected tasks section.

_Done_: the next issue exists, open, correctly labelled, on the Later milestone.

### 12. Open the closing PR

Push a branch with the run's changes as logical commits. Open **one** PR into `main` with `Closes #<current-issue>` in the body and the issue's Later milestone. Stop — the developer merges; the merge closes the issue.

_Done_: the PR is open and references the current issue.

## Adding tasks (outside the skill)

Adding a task to the open issue is **not** part of this skill — it needs explicit user approval and happens outside the run. See `docs/agents/housekeeping.md`.

## Reference

- **Reports** — the issue's Reports section, filled per the formats in `.github/ISSUE_TEMPLATE/housekeeping.md`.
- **Cascade** — the current issue closes via the closing PR's `Closes #<n>` keyword; the next issue is created as the final task.
- **Milestone** — the Later tier, rolled forward each release; the current Later milestone title is read from `docs/agents/milestones.md`.
