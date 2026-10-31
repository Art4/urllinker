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
use Art4\UrlLinker\LegacyAliasLoader;
use Art4\UrlLinker\UrlLinker;
use Art4\UrlLinker\UrlLinkerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Youthweb\UrlLinker\DomainStorage as LegacyDomainStorage;
use Youthweb\UrlLinker\UrlLinker as LegacyUrlLinker;
use Youthweb\UrlLinker\UrlLinkerInterface as LegacyUrlLinkerInterface;

/**
 * Locks the backward-compatibility contract of the legacy namespace.
 *
 * The canonical namespace is Art4\UrlLinker; the legacy Youthweb\UrlLinker
 * names must keep working as aliases so existing consumers do not break.
 * See docs/adr/0002-namespace-from-youthweb-to-art4.md.
 *
 * Loading a legacy name triggers a deprecation notice, once per name and process. Every test
 * runs in a process of its own, so that no earlier test has loaded the name already.
 */
#[CoversClass(LegacyAliasLoader::class)]
#[CoversClass(UrlLinker::class)]
#[Medium]
#[RunTestsInSeparateProcesses]
final class LegacyNamespaceTest extends TestCase
{
    #[IgnoreDeprecations]
    public function testConstructingLegacyUrlLinkerWorks(): void
    {
        $urlLinker = new LegacyUrlLinker();

        $this->assertInstanceOf(UrlLinker::class, $urlLinker);
        $this->assertSame(UrlLinker::class, $urlLinker::class);
    }

    #[IgnoreDeprecations]
    public function testLegacyInterfaceMatchesCanonical(): void
    {
        $urlLinker = new LegacyUrlLinker();

        $this->assertInstanceOf(UrlLinkerInterface::class, $urlLinker);
        $this->assertInstanceOf(LegacyUrlLinkerInterface::class, $urlLinker);
        $this->assertInstanceOf(LegacyUrlLinkerInterface::class, new UrlLinker());
    }

    #[IgnoreDeprecations]
    public function testLegacyDomainStorageResolvesToCanonical(): void
    {
        $this->assertSame(DomainStorage::class, (new \ReflectionClass(LegacyDomainStorage::class))->getName());
        $this->assertSame(DomainStorage::getValidTlds(), LegacyDomainStorage::getValidTlds());
    }

    #[IgnoreDeprecations]
    public function testLegacyUrlLinkerBehavesIdentically(): void
    {
        $legacy = new LegacyUrlLinker();
        $canonical = new UrlLinker();

        $this->assertSame(
            $canonical->linkUrlsAndEscapeHtml('Visit example.com and mail bob@example.org'),
            $legacy->linkUrlsAndEscapeHtml('Visit example.com and mail bob@example.org'),
        );
    }

    #[IgnoreDeprecations]
    public function testAllLegacyNamesAreRegistered(): void
    {
        $this->assertTrue(\class_exists(LegacyUrlLinker::class));
        $this->assertTrue(\interface_exists(LegacyUrlLinkerInterface::class));
        $this->assertTrue(\class_exists(LegacyDomainStorage::class));
    }

    #[DataProvider('provideLegacyNames')]
    #[IgnoreDeprecations]
    public function testLoadingALegacyNameTriggersDeprecationNotice(string $loadedName, string $legacyName, string $canonicalName): void
    {
        $this->expectUserDeprecationMessage(
            \sprintf(
                'The class "%s" is deprecated since version 2.3.0 and will be removed in version 3.0, use "%s" instead.',
                $legacyName,
                $canonicalName
            )
        );

        $this->assertTrue(\class_exists($loadedName) || \interface_exists($loadedName));
    }

    /**
     * @return \Iterator<string, array{string, string, string}>
     */
    public static function provideLegacyNames(): \Iterator
    {
        yield 'class' => [LegacyUrlLinker::class, 'Youthweb\\UrlLinker\\UrlLinker', UrlLinker::class];
        yield 'interface' => [LegacyUrlLinkerInterface::class, 'Youthweb\\UrlLinker\\UrlLinkerInterface', UrlLinkerInterface::class];
        yield 'domain storage' => [LegacyDomainStorage::class, 'Youthweb\\UrlLinker\\DomainStorage', DomainStorage::class];
        yield 'name in other case' => ['youthweb\\urllinker\\URLLINKER', 'Youthweb\\UrlLinker\\UrlLinker', UrlLinker::class];
    }

    public function testUsingOnlyTheCanonicalNamespaceTriggersNoDeprecationNotice(): void
    {
        // phpunit.xml fails every test that triggers a deprecation it does not expect.
        $this->expectNotToPerformAssertions();

        (new UrlLinker())->linkUrlsAndEscapeHtml('example.com');
        DomainStorage::getValidTlds();
    }

    public function testRegisterAddsAnAutoloader(): void
    {
        $before = \count(\spl_autoload_functions());

        (new LegacyAliasLoader())->register();

        $this->assertCount($before + 1, \spl_autoload_functions());
    }

    public function testUnknownNameInTheLegacyNamespaceIsNotAliased(): void
    {
        // phpunit.xml also fails this test when the unknown name triggers a notice.
        $this->assertFalse(\class_exists('Youthweb\\UrlLinker\\Unknown'));
    }

    public function testEachLegacyNameTriggersTheNoticeOnlyOnce(): void
    {
        $messages = [];

        \set_error_handler(static function (int $errno, string $message) use (&$messages): bool {
            if ($errno === E_USER_DEPRECATED) {
                $messages[] = $message;
            }

            return true;
        }, E_USER_DEPRECATED);

        try {
            new LegacyUrlLinker();
            new LegacyUrlLinker();
            $this->assertTrue(\interface_exists(LegacyUrlLinkerInterface::class));
            $this->assertTrue(\interface_exists(LegacyUrlLinkerInterface::class));
        } finally {
            \restore_error_handler();
        }

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('"Youthweb\\UrlLinker\\UrlLinker"', $messages[0]);
        $this->assertStringContainsString('"Youthweb\\UrlLinker\\UrlLinkerInterface"', $messages[1]);
    }
}
