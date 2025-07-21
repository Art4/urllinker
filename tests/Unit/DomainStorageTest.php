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

namespace Youthweb\UrlLinker\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Youthweb\UrlLinker\DomainStorage;

#[CoversClass(DomainStorage::class)]
class DomainStorageTest extends TestCase
{
    public function testGetValidTlds(): void
    {
        // Reset the static property to ensure a fresh state for the test
        // This is necessary because the static property may have been modified in previous tests
        // or runs, and we want to ensure that we are testing the initial state.
        $reflectionProperty = new \ReflectionProperty(DomainStorage::class, 'validTlds');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);

        $tlds = DomainStorage::getValidTlds();

        $this->assertCount(1440, $tlds);

        $this->assertSame(['.aaa' => true], array_slice($tlds, 0, 1));
    }
}
