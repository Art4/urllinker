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

namespace Art4\UrlLinker\Tests\Integration;

use Art4\UrlLinker\DomainStorage;
use Art4\UrlLinker\UrlLinker;
use Art4\UrlLinker\UrlLinkerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Locks the backward-compatibility contract of the legacy namespace.
 *
 * The canonical namespace is Art4\UrlLinker; the legacy Youthweb\UrlLinker
 * names must keep working as aliases so existing consumers do not break.
 * See docs/adr/0002-namespace-from-youthweb-to-art4.md.
 */
#[CoversClass(UrlLinker::class)]
final class LegacyNamespaceTest extends TestCase
{
    public function testConstructingLegacyUrlLinkerWorks(): void
    {
        $urlLinker = new \Youthweb\UrlLinker\UrlLinker();

        $this->assertInstanceOf(UrlLinker::class, $urlLinker);
        $this->assertSame(UrlLinker::class, $urlLinker::class);
    }

    public function testLegacyInterfaceMatchesCanonical(): void
    {
        $urlLinker = new \Youthweb\UrlLinker\UrlLinker();

        $this->assertInstanceOf(UrlLinkerInterface::class, $urlLinker);
        $this->assertInstanceOf(\Youthweb\UrlLinker\UrlLinkerInterface::class, $urlLinker);
        $this->assertInstanceOf(\Youthweb\UrlLinker\UrlLinkerInterface::class, new UrlLinker());
    }

    public function testLegacyDomainStorageResolvesToCanonical(): void
    {
        $this->assertSame(DomainStorage::class, (new \ReflectionClass(\Youthweb\UrlLinker\DomainStorage::class))->getName());
        $this->assertSame(DomainStorage::getValidTlds(), \Youthweb\UrlLinker\DomainStorage::getValidTlds());
    }

    public function testLegacyUrlLinkerBehavesIdentically(): void
    {
        $legacy = new \Youthweb\UrlLinker\UrlLinker();
        $canonical = new UrlLinker();

        $this->assertSame(
            $canonical->linkUrlsAndEscapeHtml('Visit example.com and mail bob@example.org'),
            $legacy->linkUrlsAndEscapeHtml('Visit example.com and mail bob@example.org'),
        );
    }

    public function testAllLegacyNamesAreRegistered(): void
    {
        $this->assertTrue(\class_exists(\Youthweb\UrlLinker\UrlLinker::class));
        $this->assertTrue(\interface_exists(\Youthweb\UrlLinker\UrlLinkerInterface::class));
        $this->assertTrue(\class_exists(\Youthweb\UrlLinker\DomainStorage::class));
    }
}
