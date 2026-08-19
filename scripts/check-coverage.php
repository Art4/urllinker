<?php

declare(strict_types=1);
/*
 * UrlLinker converts any web addresses in plain text into HTML hyperlinks.
 * Copyright (C) 2016-2025  Artur Weigandt  <https://wlabs.de/kontakt>

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$cloverPath = $argv[1] ?? __DIR__ . '/../build/coverage.xml';

if (! \is_file($cloverPath)) {
    \fwrite(STDERR, \sprintf('error: clover file "%s" not found' . PHP_EOL, $cloverPath));

    exit(1);
}

$xml = \simplexml_load_file($cloverPath);

if ($xml === false) {
    \fwrite(STDERR, \sprintf('error: could not parse clover file "%s"' . PHP_EOL, $cloverPath));

    exit(1);
}

$uncovered = [];

foreach ($xml->xpath('//file') as $file) {
    $filePath = (string) $file['name'];

    foreach ($file->line as $line) {
        if ((int) $line['count'] === 0) {
            $uncovered[] = \sprintf('%s:%s', $filePath, $line['num']);
        }
    }
}

if ($uncovered !== []) {
    \fwrite(STDERR, 'Code coverage is not 100%:' . PHP_EOL . '  ' . \implode(PHP_EOL . '  ', $uncovered) . PHP_EOL);

    exit(1);
}

\fwrite(STDOUT, 'Code coverage: 100%' . PHP_EOL);
