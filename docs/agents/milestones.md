# Milestones

Every **open** issue and every PR belongs to a milestone. There is no "unscheduled" state — picking a milestone is part of triage, never deferred.

Milestones follow the repository's release versions (see `docs/agents/releasing.md`): the next release, the release after that, and the next major.

## The milestone tiers

- **Next** (`2.2.0`): the default target. Things you intend to ship in the current cycle.
- **Later** (`2.3.0`): the next non-major release after `Next`. Non-breaking work that is explicitly postponed, and non-breaking deprecations.
- **Next major** (`3.0.0`): breaking changes only. Nothing lands here without an explicit breaking change; it can accumulate for years.

Not every milestone tier exists at all times — only the three above exist right now. A "later" milestone is created when a `Next` milestone releases and its unfinished items roll forward.

## Which milestone does an item get?

The milestone states where the work is **planned to be released**, which asks three questions in order:

1. **Is it a breaking change?** — the next major milestone (`3.0.0`). *Breaking* means existing consumers would break, e.g. removing a deprecated API.
2. **Is it intended to ship in the current cycle?** — `Next` (`2.2.0`), the default.
3. **Is it explicitly postponed or a non-breaking deprecation?** — `Later` (`2.3.0`).

PRs take the milestone of the issue they close; a standalone PR takes the milestone of the release it changes.

## Who assigns

- **Agents** assign a milestone during triage, defaulting to `Next` and escalating only per the rules above.
- **Humans** are the authority. An agent may suggest escalation to `Later` or the major, but must **never move issues or PRs into a different milestone on its own** — it always asks first.
- Assigning is a write to the tracker: `gh issue edit <n> --milestone <title>` / `gh pr edit <n> --milestone <title>`.

## Backfill

Milestones apply from now on. Closed/historical issues and PRs are **not** retroactively labeled; an item only ever needs a milestone while it is open.

## During a release

During a release, the release PR itself is set into the milestone being released (see `docs/agents/releasing.md`).

Releasing is human-initiated (see `docs/agents/releasing.md`). It is **the releaser's responsibility** to move all open issues and PRs of the released milestone into the next milestone.

If an agent is asked to prepare a release and the milestone being released is not empty, the agent must **notify the human** that open issues/PRs still sit on that milestone and must not move them itself.

When the released milestone is empty, the agent checks that the next **two** non-major slots are populated. For each missing slot it **offers the human to create** that milestone — it never creates one on its own. Currently `2.2.0` (next) and `2.3.0` (after next) exist; after `2.2.0` ships, `2.3.0` becomes the next slot, so the offer is to create `2.4.0`.
