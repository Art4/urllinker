# UrlLinker

A library that converts web and email addresses in text into HTML hyperlinks. The repository also contains the development environment (Docker container, Makefile) and CI configuration that maintain and test it.

## Language

**Supported range**:
The set of PHP versions a release of the library declares compatibility with, expressed by the `php` constraint in `composer.json`. The matrix in `ci.yml` must cover every version in the supported range.
_Avoid_: supported PHP, PHP versions

**EOL drop**:
Removing the oldest versions from the supported range once they reach their end-of-life date. Being EOL alone makes a version eligible; the drop is executed deliberately (e.g. in yearly housekeeping), not automatically on the EOL date.
_Avoid_: version bump, cleanup

**Dev container floor**:
The PHP version the Docker dev container runs — always the lowest version in the supported range, so the full local check (`make qa`) guards the oldest environment the library promises.
_Avoid_: container PHP, dev version

**Canary**:
A PHP version running in the CI matrix before its stable release, installed as a nightly dev build. It exists only to catch breaking changes early and is not advertised as "supported" until the stable release is out and the matrix entry switches from the nightly build to the stable version.
_Avoid_: preview support, 8.6 support, early support

## Library domain

**URL**:
An address the library recognizes in text — optionally with scheme, host, port, path, query and fragment. The "reference" behind RFC 3986.
_Avoid_: web address, link address

**HTML character reference**:
A `&...;` sequence (named, `&#N;` decimal or `&#xH;` hexadecimal) that stands for one character in the source text. Where a reference appears *inside a URL*, the URL extends across it; where it flanks the start or end of a URL, it belongs to the HTML, not the URL.
_Avoid_: entity, HTML entity, character entity

**Link**:
The markup produced for a recognized URL, by default `<a href="…">…</a>`, overridable via the link creators.
_Avoid_: hyperlink

**Trusted HTML**:
HTML that is assumed to be both valid and safe. `linkUrlsInTrustedHtml()` passes tags and character references through unchanged and links only URLs found in text nodes.
_Avoid_: safe HTML, pre-sanitized HTML

**Canonical namespace**:
The `Art4\UrlLinker` namespace under which all public classes are defined. New code and documentation should use it; the legacy namespace is only kept for consumers who have not migrated yet.
_Avoid_: Youthweb namespace, new namespace

**Legacy namespace**:
The `Youthweb\UrlLinker` namespace kept working via class aliases for backward compatibility after the canonical namespace moved to `Art4\UrlLinker`. It is deprecated and emits no runtime notice; code reports the canonical name via `get_class()`.
_Avoid_: old namespace, backward-compat namespace

## Releasing

**Release**:
A version of the library published to its consumers: a git tag on `main` plus a GitHub Release, picked up by Packagist. The version number is derived from the `[Unreleased]` section of `CHANGELOG.md` — a breaking change makes it a major, new behavior a minor, fixes only a patch. The decision to release belongs to the developer, who chooses the moment; it is never initiated by an agent.
_Avoid_: ship, cut a version, publish a version

## Housekeeping

**Housekeeping**:
The recurring maintenance cycle of this repository, tracked in a single open GitHub issue labelled `housekeeping`. It covers dependency updates, CI maintenance, PHP release/EOL management, TLD list refreshes, tooling updates, and small collected tasks. A run is initiated by the developer; it never makes breaking changes.
_Avoid_: chore, cleanup backlog, maintenance sprint

**Housekeeping issue**:
The always-open issue tracking the current housekeeping cycle — a checklist, a Collected tasks backlog, and a Reports record. The run creates the next issue as its final task and opens a closing PR; the PR's merge closes the issue. It rides the Later milestone, rolling forward each release.
_Avoid_: cleanup issue, maintenance issue

**Collected tasks**:
Small housekeeping tasks found during development and appended to the housekeeping issue's Collected tasks section. Adding one requires explicit user approval; agents only suggest.
_Avoid_: backlog items, cleanup list
