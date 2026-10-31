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

namespace Art4\UrlLinker;

use PHPMD\Attribute\SuppressWarnings;
use PHPMD\Rule\CleanCode\ErrorControlOperator;

/**
 * Creates the aliases of the legacy namespace `Youthweb\UrlLinker` when one of them is loaded.
 *
 * Loading a legacy name triggers a muted `E_USER_DEPRECATED` notice, once per name, and then
 * aliases it to the canonical class. Code that never uses the legacy namespace never loads it,
 * so it gets no notice. See docs/adr/0002-namespace-from-youthweb-to-art4.md.
 *
 * @internal
 */
final class LegacyAliasLoader
{
    /**
     * Lower-cased legacy name => [legacy name as documented, canonical name]
     */
    private const ALIASES = [
        'youthweb\\urllinker\\urllinker' => ['Youthweb\\UrlLinker\\UrlLinker', UrlLinker::class],
        'youthweb\\urllinker\\urllinkerinterface' => ['Youthweb\\UrlLinker\\UrlLinkerInterface', UrlLinkerInterface::class],
        'youthweb\\urllinker\\domainstorage' => ['Youthweb\\UrlLinker\\DomainStorage', DomainStorage::class],
    ];

    public function register(): void
    {
        \spl_autoload_register($this->load(...));
    }

    public function load(string $name): void
    {
        $alias = self::ALIASES[\strtolower($name)] ?? null;

        if ($alias === null) {
            return;
        }

        [$legacyName, $canonicalName] = $alias;

        $this->triggerDeprecation($legacyName, $canonicalName);

        \class_alias($canonicalName, $name);
    }

    /**
     * The notice is muted with "@" like the other deprecations of this library, see
     * docs/deprecations.md.
     */
    #[SuppressWarnings(ErrorControlOperator::class)]
    private function triggerDeprecation(string $legacyName, string $canonicalName): void
    {
        @\trigger_error(
            \sprintf(
                'The class "%s" is deprecated since version 2.3.0 and will be removed in version 3.0, use "%s" instead.',
                $legacyName,
                $canonicalName
            ),
            E_USER_DEPRECATED
        );
    }
}
