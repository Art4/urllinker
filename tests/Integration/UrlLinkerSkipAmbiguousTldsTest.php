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

use Art4\UrlLinker\UrlLinker;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(UrlLinker::class, 'linkUrlsAndEscapeHtml')]
#[CoversMethod(UrlLinker::class, 'linkUrlsInTrustedHtml')]
final class UrlLinkerSkipAmbiguousTldsTest extends UrlLinkerTestCase
{
    #[DataProvider('providerSkipAmbiguousTlds')]
    public function testSkipAmbiguousTldsInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker(['skipAmbiguousTlds' => true]);

        $this->runBothEntryPoints($urlLinker, $text, $expectedLinked, $message);
    }

    /**
     * @return \Iterator<string, array<int, string|null>>
     */
    public static function providerSkipAmbiguousTlds(): \Iterator
    {
        yield 'bare zip filename stays plain' => [
            'foobar.zip',
            'foobar.zip',
        ];
        yield 'bare java filename stays plain' => [
            'Main.java',
            'Main.java',
        ];
        yield 'bare markdown filename stays plain' => [
            'README.md',
            'README.md',
        ];
        yield 'bare python filename stays plain' => [
            'app.py',
            'app.py',
        ];
        yield 'subdomain ending in ambiguous tld stays plain' => [
            'sub.foobar.zip',
            'sub.foobar.zip',
        ];
        yield 'mixed case filename stays plain' => [
            'File.JAVA',
            'File.JAVA',
        ];
        yield 'address with scheme is still linked' => [
            'https://example.zip',
            self::link('https://example.zip', 'example.zip'),
        ];
        yield 'address with path is still linked' => [
            'dl.zip/file',
            self::link('http://dl.zip/file', 'dl.zip/file'),
        ];
        yield 'address with query is still linked' => [
            'example.zip?x=1',
            self::link('http://example.zip?x=1', 'example.zip'),
        ];
        yield 'address with fragment is still linked' => [
            'example.zip#frag',
            self::link('http://example.zip#frag', 'example.zip'),
        ];
        yield 'email address is still linked' => [
            'user@example.zip',
            self::link('mailto:user&#64;example.zip', 'user&#64;example.zip'),
        ];
        yield 'genuine tld is still linked' => [
            'example.com',
            self::link('http://example.com', 'example.com'),
        ];
    }

    #[DataProvider('providerOverriddenAmbiguousTlds')]
    public function testOverriddenAmbiguousTldsInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'skipAmbiguousTlds' => true,
            'ambiguousTlds' => ['.zip' => true],
        ]);

        $this->runBothEntryPoints($urlLinker, $text, $expectedLinked, $message);
    }

    /**
     * @return \Iterator<string, array<int, string|null>>
     */
    public static function providerOverriddenAmbiguousTlds(): \Iterator
    {
        yield 'tld kept on the override list stays plain' => [
            'foobar.zip',
            'foobar.zip',
        ];
        yield 'tld dropped from the override list is linked again' => [
            'app.py',
            self::link('http://app.py', 'app.py'),
        ];
    }

    #[DataProvider('providerSkipAmbiguousTldsWinsOverValidTlds')]
    public function testSkipAmbiguousTldsWinsOverValidTlds(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'skipAmbiguousTlds' => true,
            'validTlds' => ['.zip' => true, '.com' => true],
        ]);

        $this->runBothEntryPoints($urlLinker, $text, $expectedLinked, $message);
    }

    /**
     * @return \Iterator<string, array<int, string|null>>
     */
    public static function providerSkipAmbiguousTldsWinsOverValidTlds(): \Iterator
    {
        yield 'whitelisted ambiguous tld is still suppressed' => [
            'foobar.zip',
            'foobar.zip',
        ];
        yield 'whitelisted genuine tld is still linked' => [
            'example.com',
            self::link('http://example.com', 'example.com'),
        ];
    }

    public function testAmbiguousTldsAreLinkedByDefault(): void
    {
        $this->runBothEntryPoints(
            new UrlLinker(),
            'foobar.zip',
            self::link('http://foobar.zip', 'foobar.zip')
        );
    }

    public function testCustomHtmlLinkCreatorStillWorksWithTheGuardEnabled(): void
    {
        $urlLinker = new UrlLinker([
            'skipAmbiguousTlds' => true,
            'htmlLinkCreator' => fn($url, $content): string => '<a href="' . $url . '" target="_blank">' . $content . '</a>',
        ]);

        $text = 'see example.com and foobar.zip';
        $expected = 'see <a href="http://example.com" target="_blank">example.com</a> and foobar.zip';

        $this->assertSame($expected, $urlLinker->linkUrlsAndEscapeHtml($text));
        $this->assertSame($expected, $urlLinker->linkUrlsInTrustedHtml($text));
    }

    private function runBothEntryPoints(UrlLinker $urlLinker, string $text, string $expectedLinked, ?string $message = null): void
    {
        $this->assertSame(
            $expectedLinked,
            $urlLinker->linkUrlsAndEscapeHtml($text),
            'Simple case (escape HTML): ' . $message
        );

        $this->assertSame(
            \sprintf('foo %s bar', $expectedLinked),
            $urlLinker->linkUrlsAndEscapeHtml(\sprintf('foo %s bar', $text)),
            'Text around (escape HTML): ' . $message
        );

        // Disabled addresses are properly escaped in the escape-HTML entry point.
        $this->assertSame(
            \sprintf('&lt;div class=&quot;test&quot;&gt; %s &lt;/div&gt;', $expectedLinked),
            $urlLinker->linkUrlsAndEscapeHtml(\sprintf('<div class="test"> %s </div>', $text)),
            'Html around (escape HTML): ' . $message
        );

        $this->assertSame(
            $expectedLinked,
            $urlLinker->linkUrlsInTrustedHtml($text),
            'Simple case (trusted HTML): ' . $message
        );

        $this->assertSame(
            \sprintf('<div class="test">%s</div>', $expectedLinked),
            $urlLinker->linkUrlsInTrustedHtml(\sprintf('<div class="test">%s</div>', $text)),
            'Html around (trusted HTML): ' . $message
        );
    }
}
