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

/*
 * Compatibility layer for the legacy namespace.
 *
 * The canonical namespace is Art4\UrlLinker. The old Youthweb\UrlLinker
 * names are kept working as aliases so existing consumers do not break.
 * The aliases are created when a legacy name is loaded, and loading it
 * triggers a deprecation notice. See Art4\UrlLinker\LegacyAliasLoader and
 * docs/adr/0002-namespace-from-youthweb-to-art4.md for the rationale.
 */

// The loader is registered when this file is loaded by Composer's "files"
// autoloader, i.e. during the bootstrap phase before any code coverage
// collection is active. LegacyAliasLoader::register() is covered by
// tests/Integration/LegacyNamespaceTest.php.
// @codeCoverageIgnoreStart
(new Art4\UrlLinker\LegacyAliasLoader())->register();
// @codeCoverageIgnoreEnd
