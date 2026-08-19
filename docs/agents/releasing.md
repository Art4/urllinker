# Releasing

How a new version of the library gets released. A release is **developer-initiated and never agent-launched**: the developer picks the moment; an agent may prepare work up to the developer's gate but never starts the process or executes the irreversible steps on its own.

Both a developer and an agent can follow this procedure. Each step ends with a completion criterion — the condition that tells you the step is done. Steps 6–8 are developer-only: don't hand an agent the keyboard for them.

## Entry

The developer decides a release is warranted, and `main` is in the state they intend to release (pending feature branches and their version impact are their call). If the `[Unreleased]` section of `CHANGELOG.md` contains only "Nothing yet.", the answer is *nothing to release* — stop.

The milestone of the upcoming version must be **empty before release** (see `docs/agents/milestones.md`): it is the releaser's responsibility to move open issues and PRs on that milestone into the next milestone. An agent preparing a release must notify the human if the milestone is not empty and must never move those items itself.

## Steps

**1. Propose the version** [agent proposes, developer confirms]
Read the `[Unreleased]` section and classify it: any breaking change → major, any new behavior → minor, fixes only → patch. Present the proposed version number and the classification rationale.
_Done_: the developer confirms the version (or corrects it).

**2. Supported-range integrity gate** [blocking]
If `[Unreleased]` claims a supported-range change — an EOL drop or a widened `php` constraint — verify `composer.json`'s `php` constraint and the matrix in `ci.yml` match that claim. A mismatch is a hard stop: resolve it before proceeding. A **canary** in the matrix needs no action — it is not yet part of the declared supported range.
_Done_: no claim with a mismatch; or the mismatch is resolved.

**3. Prepare the release branch**
From up-to-date `main`, create the branch `release-<X.Y.Z>` and rewrite `CHANGELOG.md`:

- Move the `[Unreleased]` categories verbatim into a `## [X.Y.Z](https://github.com/Art4/urllinker/compare/<previous>...X.Y.Z) - <date>` section, where `<previous>` is the current released tag.
- Add a fresh top-level `[Unreleased]` with `compare/X.Y.Z...main` whose body is exactly "Nothing yet.".

```bash
git fetch origin
git checkout -b release-<X.Y.Z> origin/main
```
_Done_: the changelog diff shows only the sections moved; no entry was dropped or reworded.

**4. Validate locally**
Run `make qa` inside the Docker dev container.
_Done_: the full check passes (phpunit, phpstan, codestyle).

**5. Open the PR** [agent proposes draft, developer approves]
```
git push origin release-<X.Y.Z>
```
Propose the PR to the developer **before opening it** — draft the title and body, and ask for permission to open. Never open a PR on your own.

Draft a PR into `main` (e.g. `gh pr create --base main --head release-<X.Y.Z> --title "Release <X.Y.Z>"`):
- Title: `Release <X.Y.Z>`.
- Body: do not list what changed — that is visible in the diff. State instead that once this PR lands, version `<X.Y.Z>` will be released.

Once the developer approves and the PR is open, wait for CI.
_Done_: every job in the 8.2–8.6 matrix is green — **including the canary**. Canary red is a blocker; a release does not ship over a red matrix.

**6. Merge** [developer-only]
The developer reviews the wording and the version, then merges once CI is green.
_Done_: the merge lands on `main`.

**7. Tag** [developer-only]
On the merged `main` commit, create a lightweight tag named exactly `X.Y.Z` (no `v` prefix — matching repo history) and push it:
```
git fetch origin
git tag X.Y.Z
git push origin X.Y.Z
```
_Done_: `git ls-remote --tags origin` shows `X.Y.Z` pointing at the merged commit.

**8. Publish the GitHub Release** [developer-only]
Create a GitHub Release at tag `X.Y.Z`, body a copy of the released changelog section (e.g. `gh release create X.Y.Z --notes-file <released-section.md>`).
_Done_: the Release is visible at tag `X.Y.Z`.

**9. Verify on Packagist** [informational — not blocking]
Check that `youthweb/urllinker` on Packagist shows `X.Y.Z`. The webhook is automatic on tag push; lag is expected.
_Done_: the new version is live on Packagist.

## Reference

- **Version classification** — breaking change in `[Unreleased]` → major; new behavior → minor; fixes only → patch. The type is read from the changelog, never the developer's mood.
- **Supported-range gate interpretation** — a release publicly restates the supported range, so the code and the claim must agree. See `CONTEXT.md` for **supported range** and **EOL drop**. A **canary** is not part of the supported range until its stable release.
- **Keep a Changelog** — the changelog must follow the repo's existing conventions: section headers with `compare/<from>...<to>` links, ISO dates, and category grouping (Added / Changed / Deprecated / Removed / Fixed / Security). The placeholder for an untouched `[Unreleased]` is exactly "Nothing yet.".
- **Irreversible = the developer's hands** — merging the PR, pushing the tag, and publishing the GitHub Release are developer-only. An agent prepares up to the PR and stops.