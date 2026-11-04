# Namespace moved from Youthweb to Art4, kept alive via aliases

Status: accepted

Following the repository move from `youthweb/urllinker` to `Art4/urllinker`, the public namespace `Youthweb\UrlLinker` did not match the project's new home. We made `Art4\UrlLinker` the canonical namespace for all public classes (`UrlLinker`, `UrlLinkerInterface`, `DomainStorage`) and kept the legacy `Youthweb\UrlLinker` names working as `class_alias` aliases, registered eagerly by a composer `files` bootstrap in `src/compat.php`. This moves the identity forward without breaking existing consumers and without a major release.

## Considered options

- **Keep `Youthweb\UrlLinker` canonical and alias `Art4` on top** — rejected: the old name would remain the real identity forever; the rename would be cosmetic and the legacy name would never be removable.
- **Alias via inline `class_alias()` at the bottom of each source file with two PSR-4 maps into `src`** — rejected: fragile under `classmap-authoritative` autoloading and composer would be loading files whose declared class does not match the requested name.
- **Duplicate full class implementations under both namespaces** — rejected: double maintenance and divergent behavior for no benefit.
- **Runtime deprecation notice on legacy use** — rejected ([issue #33](https://github.com/Art4/urllinker/issues/33)): the aliases are created eagerly and share one class entry with the canonical classes, so a legacy use cannot be told from a canonical one. A notice needs the alias to be created lazily by an autoload shim when a legacy name is loaded, which changes when the aliases exist: `instanceof` and type hints do not load classes, so a type hint on a legacy name would no longer be satisfied by an `Art4\UrlLinker` object created elsewhere. That is a break of the interchangeability promised here, for the sake of a notice. The deprecation is announced in the CHANGELOG and in `UPGRADE.md` instead.
- **Proxy classes in the legacy namespace that trigger the notice** — rejected: the legacy classes would no longer be the canonical ones, so the class identity promised here would break; `final` would have to go and the interface hierarchy would be inverted.

## Consequences

- New code uses `Art4\UrlLinker\...`; legacy `Youthweb\UrlLinker\...` names still resolve and are interchangeable for `instanceof`, type hints, and usage.
- Because a `class_alias` shares one class entry, `get_class()` and `serialize()` report the canonical `Art4\UrlLinker\...` name even for instances constructed through the legacy names — a non-breaking behavioral nuance.
- The legacy namespace is deprecated since 2.3.0 but deliberately silent: the CHANGELOG and `UPGRADE.md` announce it, no notice is triggered. It is removed in the next major ([issue #35](https://github.com/Art4/urllinker/issues/35)).
- The `composer` package name `youthweb/urllinker` is unchanged.