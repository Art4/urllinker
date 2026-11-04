---
name: Housekeeping
about: Recurring maintenance bucket — the task list, Collected tasks backlog, and Reports record of a housekeeping pass.
labels: housekeeping
title: "Housekeeping <YYYY-MM>"
---

## Tasks

Rhythm: monthly

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
- [ ] Update the copyright year in the file headers (`2016-<current year>`) when the year has changed since the previous Housekeeping ticket
- [ ] Scan the Git history for committed secrets: the whole history the first time, afterwards the commits added since the previous Housekeeping ticket's scan; note the commit scanned up to
- [ ] Re-check the project's quality tooling: is every tool set up so far (PHPUnit, PHPStan, PHPMD, php-cs-fixer, Rector, the Roave backward compatibility check) still in place and running, and is there a further one to set up? File a ticket for each gap
- [ ] After the dependency update, re-run PHPStan and check for newly surfaced deprecation warnings; fix them where they are
- [ ] Measure PHPMD again: for each metric rule whose peak lies below its limit in `phpmd.xml`, lower the limit to the peak and update its comment (config only, never the code); after `composer update`, handle rules a new PHPMD version added: a new metric rule gets today's peak as its limit, any other new finding is fixed or suppressed with a reason
- [ ] Look for a newer PER-CS version than the one `.php-cs-fixer.dist.php` pins (`vendor/bin/php-cs-fixer list-sets` after `composer update`); where there is one, switch to it and set each rule that now reports findings to `false` with `Not yet:` and its count (config only, never the code)
- [ ] After a release, remove each `<ignored-regex>` entry of the Roave backward compatibility check that the release made obsolete, so the check compares against the new tag
- [ ] Create the next Housekeeping ticket from this template, due one rhythm from today (roll over unfinished tasks)

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
