# Deprecations

How the library deprecates a part of its public API. The promise to consumers and the release steps are in [the backward compatibility promise](bc-promise.md); this file describes what a deprecation looks like in the code. It follows the pattern of the 1.x releases.

## The notice

A deprecation triggers a **muted** `E_USER_DEPRECATED` notice:

```php
@\trigger_error(
    'The config option "cutUrlsAtEntities" is deprecated since version 2.3.0 and will be removed in version 3.0, do not set it: …',
    E_USER_DEPRECATED
);
```

- The `@` mutes it. It reaches only code that installs its own error handler (a test suite, a framework's deprecation collector) and does not show up in the output of other callers.
- PHPMD flags the `@` as `ErrorControlOperator`. The call lives in a small private method of its own, which carries `#[SuppressWarnings(ErrorControlOperator::class)]` and a comment saying why.
- The message reads: what is deprecated, since which version, in which version it is removed, and what to do instead.

## When it fires

- **A config option** is checked in the constructor, once per instance, right after the option is read and only when the deprecated value is used. Not setting the option, or setting it to the value that is already the default, stays silent.
- **A method or class** triggers it when it is called or used.
- **The legacy namespace** `Youthweb\UrlLinker` triggers it when a legacy name is loaded, once per name: `LegacyAliasLoader` is an autoload shim that triggers the notice and then creates the alias.

## In the code and the docs

- A deprecated method or class carries `@deprecated since version X, to be removed in Y. Use … instead.`
- A deprecated **config option** is described in the prose of the constructor's `@param` text, not with a `@deprecated` tag: a tag on the constructor would flag every `new UrlLinker()` in PHPStan's deprecation rules.
- The CHANGELOG lists it under `### Deprecated` in the release that deprecates it.
- The README marks it as deprecated and no longer uses it in examples.

## Tests

`phpunit.xml` makes PHPUnit see the muted notices (`ignoreSuppressionOfDeprecations`) and fail on every deprecation a test does not expect (`failOnDeprecation`). A deprecation is tested with PHPUnit's own means, see `tests/Unit/UrlLinkerTest.php`:

- **The deprecated use** triggers the notice: the test has `#[IgnoreDeprecations]` and calls `expectUserDeprecationMessage()` with the message. It fails when the notice is missing or its text differs. Without the attribute PHPUnit still reports and fails on an expected deprecation.
- **The other uses** do not trigger it: the test just does the call, with `expectNotToPerformAssertions()`. `failOnDeprecation` fails it when a notice comes.
- **A test that runs the deprecated use for another purpose** has `#[IgnoreDeprecations]` only, like the test of the legacy behavior.

PHPUnit does not check that the notice came exactly once.

## Removal

The next major release removes the part, its notice and its tests. The CHANGELOG lists the removal as **BREAKING**.
