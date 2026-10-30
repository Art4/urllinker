# Tests

| Folder | For |
|---|---|
| `tests/Unit/` | One class per test class, following PSR-4 |
| `tests/Integration/` | Real collaborators together, no test doubles |

A folder only gets created once something needs it.

## Coverage metadata

PHPUnit runs with `requireCoverageMetadata` and `beStrictAboutCoverageMetadata`: a test that runs code it neither covers nor uses is risky, and risky tests fail the build.

- A unit test has exactly one `#[CoversClass]` and lists every other class it runs as `#[UsesClass]`.
- An integration test is `#[Medium]`. It runs real collaborators, so PHPUnit does not ask it to name everything it runs; it keeps its `#[CoversClass]` or `#[CoversMethod]`.
