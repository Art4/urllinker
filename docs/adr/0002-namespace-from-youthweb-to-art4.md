# Namespace moved from Youthweb to Art4, kept alive via aliases

Status: accepted

Following the repository move from `youthweb/urllinker` to `Art4/urllinker`, the public namespace `Youthweb\UrlLinker` did not match the project's new home. We made `Art4\UrlLinker` the canonical namespace for all public classes (`UrlLinker`, `UrlLinkerInterface`, `DomainStorage`) and kept the legacy `Youthweb\UrlLinker` names working as `class_alias` aliases, registered eagerly by a composer `files` bootstrap in `src/compat.php`. This moves the identity forward without breaking existing consumers and without a major release.

## Considered options

- **Keep `Youthweb\UrlLinker` canonical and alias `Art4` on top** — rejected: the old name would remain the real identity forever; the rename would be cosmetic and the legacy name would never be removable.
- **Alias via inline `class_alias()` at the bottom of each source file with two PSR-4 maps into `src`** — rejected: fragile under `classmap-authoritative` autoloading and composer would be loading files whose declared class does not match the requested name.
- **Duplicate full class implementations under both namespaces** — rejected: double maintenance and divergent behavior for no benefit.
- **Runtime deprecation notice on legacy use** — deferred: arbitrary `E_USER_DEPRECATED` could itself break consumers with strict error handlers, and this is a 2.x minor. Tracked in [issue #33](https://github.com/Art4/urllinker/issues/33) for the future.

## Consequences

- New code uses `Art4\UrlLinker\...`; legacy `Youthweb\UrlLinker\...` names still resolve and are interchangeable for `instanceof`, type hints, and usage.
- Because a `class_alias` shares one class entry, `get_class()` and `serialize()` report the canonical `Art4\UrlLinker\...` name even for instances constructed through the legacy names — a non-breaking behavioral nuance.
- The legacy namespace is deprecated but deliberately silent; it can be removed in a future major.
- The `composer` package name `youthweb/urllinker` is unchanged.