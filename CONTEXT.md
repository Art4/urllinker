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
