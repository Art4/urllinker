---
name: Housekeeping
about: Recurring maintenance bucket — the task list, Collected tasks backlog, and Reports record of a housekeeping pass.
labels: housekeeping
title: "Housekeeping <YYYY-MM>"
---

## Tasks

The tasks below are part of the current housekeeping pass. The issue can be resolved by running the `/housekeeping` skill.

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

<details>
<summary>Composer update</summary>

Paste the condensed `composer update` output — one line per changed package.

- `package` — `from` → `to`
</details>

<details>
<summary>Composer audit</summary>

Paste the `composer audit` output.

- _(advisories, or "no known vulnerabilities")_
</details>

<details>
<summary>PHP versions</summary>

Paste the release/EOL status of each matrix version, noting promoted canaries and proposed drops.

- `version` — `status` (stable / canary / EOL; note promoted or dropped versions)
</details>

<details>
<summary>CI actions</summary>

Paste the used vs latest version per action and any GitHub deprecation warnings.

- `action` — `used` → `latest`
- GitHub deprecation warnings: _(none / list)_
</details>

<details>
<summary>TLD list</summary>

Paste the IANA list version before and after the refresh.

- IANA list version: `before` → `after`
</details>

<details>
<summary>Tooling</summary>

Paste the tool versions and any rector / php-cs-fixer rules absorbed.

- `tool` — `version`; rules absorbed: _(list)_
</details>
