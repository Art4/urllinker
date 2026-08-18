# Housekeeping

Recurring maintenance is tracked as a single always-open GitHub issue labelled `housekeeping`. A housekeeping run — invoked with the `housekeeping` skill — works the issue's task list and Collected tasks, records reports, creates the next issue as its final task, and opens one closing PR whose merge closes the current issue.

## Cadence

Housekeeping runs **regularly**: quarterly plus before each release. The developer initiates a run by invoking the housekeeping skill; nothing schedules itself.

## Issue conventions

- **Label**: `housekeeping`. **Milestone**: the Later tier — currently `2.3.0` — rolling forward each release (see `docs/agents/milestones.md`).
- One open issue at a time; the run creates the next one as its final task, using `.github/ISSUE_TEMPLATE/housekeeping.md`.
- The current issue closes when the closing PR merges (the PR carries `Closes #<n>`).

## Collecting tasks

During any development work, an agent that spots something housekeeping-worthy — a small refactoring note, an outdated dependency, a stale doc, a deprecated API in a dependency, TLD drift — **suggests** to the user that it be added to the open issue. Appending a `- [ ] <task>` line to the Collected tasks section happens **only with explicit user approval**. Agents never add or edit the issue on their own.

Housekeeping accepts only **small, bounded, non-urgent** tasks:

- A task that proves too big is re-filed as its own regular issue and removed from the backlog.
- Urgent findings (security advisories, blockers) are never housekeeping — they get an immediate issue and a fix PR outside the flow.
- Housekeeping never makes breaking changes; dropping PHP version support requires human approval.

## Running a session

Invoke the `housekeeping` skill and follow its process — it selects the issue (asking when none or several are open), works the task list and Collected tasks, records the Reports section, creates the next issue, and opens the closing PR. The developer merges the PR; the merge closes the issue. The mechanics live in the skill; this file holds the conventions.

## Reports

The issue's Reports section is filled by the run:

- **Composer update** — from → to per package
- **Composer audit** — advisories, or "no known vulnerabilities"
- **PHP versions** — release/EOL status per matrix version; canaries promoted/added; EOL flags; approved supported-range changes
- **CI actions** — used vs latest per action; GitHub deprecation warnings
- **TLD list** — IANA version before → after
- **Tooling** — rector / php-cs-fixer rule changes; tool versions
