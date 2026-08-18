---
name: Housekeeping
about: Recurring maintenance bucket — the task list, Collected tasks backlog, and Reports record of a housekeeping pass.
labels: housekeeping
title: "Housekeeping <YYYY-MM>"
---

## Tasks

Work through the tasks below. The last task creates the next issue; when every task is done, open one PR that closes this issue.

- [ ] Update Composer dependencies and record the result in Reports
- [ ] Run `composer audit` and record the result in Reports
- [ ] Update CI action versions and check GitHub deprecation warnings; record in Reports
- [ ] Check the PHP release/EOL calendar; apply canary changes, propose version drops; record in Reports
- [ ] Verify supported-range integrity: composer.json ↔ ci.yml ↔ dev container floor ↔ docs
- [ ] Refresh the IANA TLD list
- [ ] Re-run rector / php-cs-fixer and absorb new rules
- [ ] Complete the Collected tasks
- [ ] Update docs and CHANGELOG
- [ ] Run `make qa`
- [ ] Create the next housekeeping issue from the template (roll over unfinished tasks)

## Collected tasks

Small housekeeping tasks found during development. Append as `- [ ] <task>`. Unfinished tasks roll into the next issue.

- [ ] _(none yet)_

## Reports

Fill in each section as its task completes.

### Composer update

- `package` — `from` → `to`

### Composer audit

- _(advisories, or "no known vulnerabilities")_

### PHP versions

- `version` — `status` (stable / canary / EOL; note promoted or dropped versions)

### CI actions

- `action` — `used` → `latest`
- GitHub deprecation warnings: _(none / list)_

### TLD list

- IANA list version: `before` → `after`

### Tooling

- `tool` — `version`; rules absorbed: _(list)_