# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/Art4/urllinker/compare/2.1.0...main)

Nothing yet.

## [2.1.0](https://github.com/Art4/urllinker/compare/2.0.0...2.1.0) - 2025-07-22

### Added

- Add support and tests for PHP 8.4 and 8.5

### Changed

- Update the IANA TLD list with version `2025072000`
- Drop support for PHP 7.4 and 8.0
- Update to PHPStan v2 and PHPUnit v12
- Update code style to latest PER-CS
- Improve code coverage reporting

## [2.0.0](https://github.com/Art4/urllinker/compare/1.5.1...2.0.0) - 2022-12-14

### Changed

- UrlLinker is now fully typed.
- **BREAKING** Return type `string` was added to method `Youthweb\UrlLinker\UrlLinkerInterface::linkUrlsAndEscapeHtml()`.
- **BREAKING** Return type `string` was added to method `Youthweb\UrlLinker\UrlLinkerInterface::linkUrlsInTrustedHtml()`.

### Removed

- **BREAKING** The deprecated support for `callable` in the config option `htmlLinkCreator` was removed, provide `Closure` instead
- **BREAKING** The deprecated support for `callable` in the config option `emailLinkCreator` was removed, provide `Closure` instead
- **BREAKING** The deprecated support for non-boolean in the config option `allowFtpAddresses` was removed, provide `boolean` instead
- **BREAKING** The deprecated support for non-boolean in the config option `allowUpperCaseUrlSchemes` was removed, provide `boolean` instead
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::getAllowFtpAddresses()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::setAllowFtpAddresses()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::getAllowUpperCaseUrlSchemes()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::setAllowUpperCaseUrlSchemes()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::getHtmlLinkCreator()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::setHtmlLinkCreator()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::getEmailLinkCreator()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::setEmailLinkCreator()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::getValidTlds()` was removed
- **BREAKING** The deprecated method `Youthweb\UrlLinker\UrlLinker::setValidTlds()` was removed

## [1.5.1](https://github.com/Art4/urllinker/compare/1.5.0...1.5.1) - 2022-12-09

### Added

- Add tests for PHP 8.3
- Add Codecov for better code coverage

### Deprecated

- Providing a `htmlLinkCreator` Closure, that does not return a `string` is deprecated, let your `Closure` always return a `string` instead.
- Providing a `emailLinkCreator` Closure, that does not return a `string` is deprecated, let your `Closure` always return a `string` instead.

## [1.5.0](https://github.com/Art4/urllinker/compare/1.4.0...1.5.0) - 2022-12-07

### Added

- Add type declarations for attributes, parameters and return values in nearly all classes
- Add tests for PHP 8.1 and 8.2

### Changed

- Update the IANA TLD list with version `2022120600`
- Change code style to follow PER
- Move CI tests from Travis-CI to Github Actions

### Deprecated

- Providing the config option `htmlLinkCreator` to `Youthweb\UrlLinker\UrlLinker::__construct()` as `callable` is deprecated, provide as `Closure` instead.
- Providing the config option `emailLinkCreator` to `Youthweb\UrlLinker\UrlLinker::__construct()` as `callable` is deprecated, provide as `Closure` instead.
- Providing the config option `allowFtpAddresses` to `Youthweb\UrlLinker\UrlLinker::__construct()` not as `boolean` is deprecated, provide as `boolean` instead.
- Providing the config option `allowUpperCaseUrlSchemes` to `Youthweb\UrlLinker\UrlLinker::__construct()` not as `boolean` is deprecated, provide as `boolean` instead.
- Implementing `Youthweb\UrlLinker\UrlLinkerInterface::linkUrlsAndEscapeHtml()` without return type `string` is deprecated, add `string` as return type in your implementation instead.
- Implementing `Youthweb\UrlLinker\UrlLinkerInterface::linkUrlsInTrustedHtml()` without return type `string` is deprecated, add `string` as return type in your implementation instead.

## [1.4.0](https://github.com/Art4/urllinker/compare/1.3.0...1.4.0) - 2021-03-05

### Added

- Add support for PHP 7.4 and PHP 8.0

### Changed

- Update the IANA TLD list with version `2021030400`
- Drop support for PHP 7.2 and 7.3

## [1.3.0](https://github.com/Art4/urllinker/compare/1.2.0...1.3.0) - 2019-10-10

### Added

- Update the IANA TLD list with 20 domains less
- Add support for PHP 7.3

### Changed

- Drop support for PHP 5.6, 7.0 and 7.1
- Change Code Style to PSR-2

## [1.2.0](https://github.com/Art4/urllinker/compare/1.1.0...1.2.0) - 2017-08-24

### Changed

- The provided config option `htmlLinkCreator` to `Youthweb\UrlLinker\UrlLinker::__construct()` can be a `callable`.
- The provided config option `emailLinkCreator` to `Youthweb\UrlLinker\UrlLinker::__construct()` can be a `callable`.
- Updated the IANA TLD list with ~50 more domains
- The test files following PSR-4

### Fixed

- Added a missing `use` for `InvalidArgumentException`
- Don't use deprecated methods internally

## [1.1.0](https://github.com/Art4/urllinker/compare/1.0.0...1.1.0) - 2017-04-10

### Added

- new constructor in `Youthweb\UrlLinker\UrlLinker` for configuration
- add `.gitattributes` file

### Deprecated

- Deprecated `Youthweb\UrlLinker\UrlLinker::setAllowFtpAddresses()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::getAllowFtpAddresses()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::setAllowUpperCaseUrlSchemes()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::getAllowUpperCaseUrlSchemes()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::setHtmlLinkCreator()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::getHtmlLinkCreator()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::setEmailLinkCreator()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::getEmailLinkCreator()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::setValidTlds()`
- Deprecated `Youthweb\UrlLinker\UrlLinker::getValidTlds()`

## [1.0.0](https://github.com/Art4/urllinker/compare/a173dfe2f6ff5a4423612b423323e94b5d2f58e2...1.0.0) - 2016-09-05

### Added

- Forked from https://bitbucket.org/kwi/urllinker
- This CHANGELOG.md
- Automated testing and code coverage with travis-ci.org and coveralls.io
- Updated the IANA TLD list
- Add your own supported TLDs
- Add a closure to modify the html link creation
- Add a closure to modify or disable the email link creation

### Changed

- Updated min. requirements to PHP 5.6
- Moved the config from the constructor to their own getter and setter methods
- Do not encode html characters twice
- Licensed under GPL3
