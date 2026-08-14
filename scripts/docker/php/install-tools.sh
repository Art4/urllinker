#!/bin/sh
set -eu

apt-get update
apt-get install -y --no-install-recommends git libonig-dev unzip

docker-php-ext-install mbstring

# xdebug is installed but inert unless XDEBUG_MODE is set (e.g. for coverage).
# It may not compile yet on unreleased PHP versions, so failure is non-fatal.
if pecl install xdebug >/tmp/xdebug-install.log 2>&1; then
	docker-php-ext-enable xdebug
	printf 'xdebug.mode=off\n' >"$PHP_INI_DIR/conf.d/xdebug-mode.ini"
else
	printf 'warning: could not install xdebug on this PHP version; code coverage is unavailable.\n' >&2
	tail -n 5 /tmp/xdebug-install.log >&2
fi

rm -rf /var/lib/apt/lists/*
