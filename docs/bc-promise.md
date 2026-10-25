# Backward compatibility promise

UrlLinker follows [Semantic Versioning 2](https://semver.org). This document states which parts of the library code outside this repository may rely on, and how a breaking change to them is allowed to happen.

## Covered

The public API is everything below, under the canonical namespace `Art4\UrlLinker` and under the legacy namespace `Youthweb\UrlLinker`:

- `UrlLinker`: the constructor, every config option it accepts with the type and default documented in the README, and the methods `linkUrlsAndEscapeHtml()` and `linkUrlsInTrustedHtml()` with their signatures.
- `UrlLinkerInterface`: its methods and signatures. Code outside may implement the interface, so adding a method to it is a breaking change.
- `DomainStorage::getValidTlds()`: the method and its return type.
- The legacy aliases `Youthweb\UrlLinker\UrlLinker`, `Youthweb\UrlLinker\UrlLinkerInterface` and `Youthweb\UrlLinker\DomainStorage` (see [ADR 0002](adr/0002-namespace-from-youthweb-to-art4.md)).
- The exception classes thrown: `InvalidArgumentException` for an invalid config option, `UnexpectedValueException` for a link creator that returns no string.
- The Composer package name `youthweb/urllinker`.

## Not covered

- **Anything marked `@internal`**: `AddressScanner`, `AddressToken`, `PlainToken`, and any class, method or constant added with that mark. They may change or disappear in any release.
- **Private and protected members** of any class.
- **Extending classes**: every class is `final`. Removing `final` would not be a breaking change; code outside never relies on subclassing.
- **The produced HTML**: which addresses are recognized and how the default link creators render them may change in any release, for example through a refreshed IANA TLD list or a fixed recognition bug. Code that needs a specific markup provides its own `htmlLinkCreator` and `emailLinkCreator`.
- **Exception messages**: their wording may change.
- **The development setup**: tests, CI, tooling configuration, `docs/` and the Docker dev container.

## How a breaking change happens

1. A minor release deprecates the part: the CHANGELOG lists it under *Deprecated*, the code carries `@deprecated` with the replacement, and the replacement is available in the same release.
2. The next major release may remove it; the CHANGELOG lists the removal as **BREAKING**.

A deprecation may be silent or trigger an `E_USER_DEPRECATED` notice; switching a silent deprecation to a notice happens in a minor release and is not a breaking change.

A patch release fixes bugs and refreshes data (such as the TLD list); it adds no public API.

## Supported PHP versions

The supported range is `require.php` in `composer.json`. A minor release may drop a PHP version that has reached its end of life; this is listed in the CHANGELOG and is not a breaking change. Dropping a still supported PHP version needs a major release.
