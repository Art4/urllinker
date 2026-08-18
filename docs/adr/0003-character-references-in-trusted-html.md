# Character references inside URLs in trusted HTML are linked, with a legacy opt-out

Status: accepted

`linkUrlsInTrustedHtml()` split URLs at every character reference because references were treated as markup (issue #2). We fixed the default so a reference to a character that may appear in a URL (e.g. `&amp;` for `&`) is part of the URL and is linked, while a reference to a character that may not (e.g. `&lt;`, `&gt;`, `&quot;`) still flanks the URL as markup. Matched URLs are decoded before reaching the link creators, and the default link creation re-escapes them, so the output equals what `linkUrlsAndEscapeHtml()` produces for the same input. The former behavior is preserved behind the `cutUrlsAtEntities` config option as a migration aid, deprecated with a runtime notice in 2.3.0 (issue #38) and removed in a future major (issue #39).

## Considered options

- **Opt-in fix, default unchanged** — rejected: the split behavior is a bug and would have remained the default; the issue would stay unresolved for everyone who does not opt in.
- **Post-match trimming of boundary references** — rejected: the URL regex cannot match a bare host followed by a reference to a non-URL character (e.g. `&lt;example.com&gt;`), so the boundary cases had to be handled at carving time instead.
- **Decode-then-link the whole text** — rejected: it would decode references outside URLs and re-encode on output, changing far more than the URL handling.
- **No opt-out** — rejected: the default output changes for existing consumers; a documented migration aid gives them a guaranteed path without a major release.

## Consequences

- The default output of `linkUrlsInTrustedHtml()` changes for inputs with a character reference inside a URL (previously cut, now linked); all other inputs are byte-for-byte unchanged.
- Custom `htmlLinkCreator`/`emailLinkCreator` closures in trusted HTML now receive the *decoded* URL (a bare `&` where the source had `&amp;`), the same form `linkUrlsAndEscapeHtml()` already provides.
- `cutUrlsAtEntities = true` reproduces the pre-fix behavior exactly and is the single migration path; it is a temporary option with a tracked deprecation and removal.