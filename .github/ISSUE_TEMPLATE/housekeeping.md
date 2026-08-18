---
name: Housekeeping
about: Recurring maintenance bucket — the checklist, Collected tasks backlog, and Reports record of a housekeeping run.
labels: housekeeping
title: "Housekeeping <YYYY-MM>"
---

## Checklist

A housekeeping run works through these in order. The final item creates the next issue; once the checklist is complete the run opens one closing PR referencing this issue, and the PR's merge closes it.

- [ ] Update Composer dependencies and record `composer update` in Reports
- [ ] Run `composer audit` and record the result in Reports
- [ ] Update CI action versions and check GitHub deprecation warnings (Reports)
- [ ] Check the PHP release/EOL calendar; propose supported-range changes (Reports)
- [ ] Verify supported-range integrity: composer.json ↔ ci.yml ↔ dev container floor ↔ docs
- [ ] Refresh the IANA TLD list (Reports)
- [ ] Re-run rector / php-cs-fixer and absorb new rules (Reports)
- [ ] Work through the Collected tasks
- [ ] Docs + CHANGELOG hygiene
- [ ] Run `make qa`
- [ ] Create the next housekeeping issue from the template (roll over unfinished tasks)

## Collected tasks

Small housekeeping tasks found during development. Append as `- [ ] <task>`. Adding a task requires explicit user approval — agents only suggest. Unfinished tasks roll into the next issue.

- [ ] _(none yet)_

## Reports

Recorded by the housekeeping run as it works.

### Composer update

### Composer audit

### PHP versions

### CI actions

### TLD list

### Tooling
