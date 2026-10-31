# Namespace moved from Youthweb to Art4, kept alive via aliases

Status: accepted

Following the repository move from `youthweb/urllinker` to `Art4/urllinker`, the public namespace `Youthweb\UrlLinker` did not match the project's new home. We made `Art4\UrlLinker` the canonical namespace for all public classes (`UrlLinker`, `UrlLinkerInterface`, `DomainStorage`) and kept the legacy `Youthweb\UrlLinker` names working as `class_alias` aliases, registered by a composer `files` bootstrap in `src/compat.php`. Since 2.3.0 the bootstrap only registers an autoload shim (`LegacyAliasLoader`); the aliases are created, and a deprecation notice is triggered, when a legacy name is loaded. This moves the identity forward without breaking existing consumers and without a major release.

## Considered options

- **Keep `Youthweb\UrlLinker` canonical and alias `Art4` on top** — rejected: the old name would remain the real identity forever; the rename would be cosmetic and the legacy name would never be removable.
- **Alias via inline `class_alias()` at the bottom of each source file with two PSR-4 maps into `src`** — rejected: fragile under `classmap-authoritative` autoloading and composer would be loading files whose declared class does not match the requested name.
- **Duplicate full class implementations under both namespaces** — rejected: double maintenance and divergent behavior for no benefit.
- **Runtime deprecation notice on legacy use** — first deferred because an arbitrary `E_USER_DEPRECATED` could break consumers with strict error handlers, then done in 2.3.0 ([issue #33](https://github.com/Art4/urllinker/issues/33)) as a muted notice (see `docs/deprecations.md`), which reaches only test suites and deprecation collectors.
- **Proxy classes in the legacy namespace that trigger the notice** — rejected: the legacy classes would no longer be the canonical ones, so the class identity promised here would break; `final` would have to go and the interface hierarchy would be inverted.

## Consequences

- New code uses `Art4\UrlLinker\...`; legacy `Youthweb\UrlLinker\...` names still resolve and are interchangeable for `instanceof`, type hints, and usage.
- Because a `class_alias` shares one class entry, `get_class()` and `serialize()` report the canonical `Art4\UrlLinker\...` name even for instances constructed through the legacy names — a non-breaking behavioral nuance.
- The legacy namespace is deprecated. Loading a legacy name triggers a muted `E_USER_DEPRECATED` notice, once per name; code that never uses the legacy namespace gets none. It can be removed in a future major ([issue #35](https://github.com/Art4/urllinker/issues/35)).
- `instanceof` and type hints do not load classes. A type hint on a legacy name is only satisfied by an `Art4\UrlLinker` object created elsewhere once the legacy name has been loaded.
- The `composer` package name `youthweb/urllinker` is unchanged.