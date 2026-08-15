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

#[CoversMethod(UrlLinker::class, 'linkUrlsInTrustedHtml')]
final class UrlLinkerInTrustedHtmlTest extends UrlLinkerTestCase
{
    #[DataProvider('provideTextsWithFtpLinksWithoutHtml')]
    public function testFtpUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'allowFtpAddresses' => true,
        ]);

        $this->runLinkUrlsInTrustedHtmlTests($urlLinker, $text, $expectedLinked, $message);
    }

    #[DataProvider('provideTextsWithUppercaseLinksWithoutHtml')]
    public function testUppercaseUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => true,
        ]);

        $this->runLinkUrlsInTrustedHtmlTests($urlLinker, $text, $expectedLinked, $message);
    }

    #[DataProvider('provideTextsNotContainingAnyUrls')]
    public function testTextNotContainingAnyUrlsRemainsTheSame(string $text): void
    {
        $this->assertSame($text, (new UrlLinker())->linkUrlsInTrustedHtml($text));
    }

    public function testExample(): void
    {
        $html = <<<EOD
            <p>Send me an <a href="bob@example.com">e-mail</a>
            at bob@example.com.</p>
            <p>This is already a link: <a href="http://google.com">http://google.com</a></p>
            <p title='10>20'>Tricky markup...</p>
            EOD;

        $expected = <<<EOD
            <p>Send me an <a href="bob@example.com">e-mail</a>
            at <a href="mailto:bob&#64;example.com">bob&#64;example.com</a>.</p>
            <p>This is already a link: <a href="http://google.com">http://google.com</a></p>
            <p title='10>20'>Tricky markup...</p>
            EOD;

        $this->assertSame($expected, (new UrlLinker())->linkUrlsInTrustedHtml($html));
    }

    #[DataProvider('provideTextsWithLinksWithoutHtml')]
    public function testUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $this->runLinkUrlsInTrustedHtmlTests(new UrlLinker(), $text, $expectedLinked, $message);
    }

    private function runLinkUrlsInTrustedHtmlTests(UrlLinker $urlLinker, string $text, string $expectedLinked, ?string $message = null): void
    {
        $this->assertSame(
            $expectedLinked,
            $urlLinker->linkUrlsInTrustedHtml($text),
            'Simple case: ' . $message
        );

        $this->assertSame(
            \sprintf('foo %s bar', $expectedLinked),
            $urlLinker->linkUrlsInTrustedHtml(\sprintf('foo %s bar', $text)),
            'Text around: ' . $message
        );

        // html should NOT get encoded
        $this->assertSame(
            \sprintf('<div class="test">%s</div>', $expectedLinked),
            $urlLinker->linkUrlsInTrustedHtml(\sprintf('<div class="test">%s</div>', $text)),
            'Html around: ' . $message
        );
    }

    #[DataProvider('provideTextsWithHtml')]
    public function testHtmlInText(string $text, string $expectedLinked): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => true,
        ]);

        $this->runLinkUrlsInTrustedHtmlTests($urlLinker, $text, $expectedLinked);
    }

    /**
     * provide html in text
     *
     * @return \Iterator<int, array<int, string>>
     */
    public static function provideTextsWithHtml(): \Iterator
    {
        yield [
            '<a href="http://example.com?a=b&amp;c=d">example.com</a>',
            '<a href="http://example.com?a=b&amp;c=d">example.com</a>',
        ];
        yield [
            '<a href="http://example.com?a=b&amp%3Bc=d">example.com</a>',
            '<a href="http://example.com?a=b&amp%3Bc=d">example.com</a>',
        ];
        yield [
            '<a href="http://example.com?a=b%26amp%3Bc=d">example.com</a>',
            '<a href="http://example.com?a=b%26amp%3Bc=d">example.com</a>',
        ];
        yield [
            'http://example.com?a=b&c=d',
            self::link('http://example.com?a=b&amp;c=d', 'example.com'),
        ];
        yield [
            'http://example.com?a=b&amp%3bc=d',
            self::link('http://example.com?a=b&amp;amp%3bc=d', 'example.com'),
        ];
        yield [
            'http://example.com?a=b&amp;c=d',
            self::link('http://example.com?a=b&amp;c=d', 'example.com'),
            'The character reference &amp; is part of the URL',
        ];
        yield [
            'http://example.com?a=b&#38;c=d',
            self::link('http://example.com?a=b&amp;c=d', 'example.com'),
            'The decimal character reference &#38; stands for the & in the URL',
        ];
        yield [
            'http://example.com?a=b&#x26;c=d',
            self::link('http://example.com?a=b&amp;c=d', 'example.com'),
            'The hexadecimal character reference &#x26; stands for the & in the URL',
        ];
        yield [
            '&lt;example.com&gt;',
            '&lt;' . self::link('http://example.com', 'example.com') . '&gt;',
            'Character references to &lt; and &gt; flank the URL and are not part of it',
        ];
        yield [
            '&lt;http://example.com&gt;',
            '&lt;' . self::link('http://example.com', 'example.com') . '&gt;',
        ];
        yield [
            'http://example.com?a=b&amp;',
            self::link('http://example.com?a=b&amp;', 'example.com'),
            'A trailing character reference belongs to the URL and its terminator is not split off',
        ];
        yield [
            'http://example.com?a=b&#38;',
            self::link('http://example.com?a=b&amp;', 'example.com'),
            'The trailing decimal character reference &#38; stands for the & in the URL',
        ];
        yield [
            'http://example.com?a=b&#x26;',
            self::link('http://example.com?a=b&amp;', 'example.com'),
            'The trailing hexadecimal character reference &#x26; stands for the & in the URL',
        ];
        yield [
            '&lt;http://example.com?a=b&amp;&gt;',
            '&lt;' . self::link('http://example.com?a=b&amp;', 'example.com') . '&gt;',
            'A flanking reference stays in the HTML, not in the URL',
        ];
        yield [
            'http://example.com?a=b&amp;</p>',
            self::link('http://example.com?a=b&amp;', 'example.com') . '</p>',
            'Markup after a URL is not touched',
        ];
        yield [
            'http://example.com?a=b;',
            self::link('http://example.com?a=b', 'example.com') . ';',
            'A semicolon that does not terminate a reference stays trailing punctuation',
        ];
        yield [
            'http://example.com/path;',
            self::link('http://example.com/path', 'example.com/path') . ';',
            'A semicolon after the path stays trailing punctuation',
        ];
    }

    public function testLegacyCutUrlsAtEntitiesOptionReproducesOldBehavior(): void
    {
        $urlLinker = new UrlLinker(['cutUrlsAtEntities' => true]);

        $cases = [
            'http://example.com?a=b&amp;c=d' => self::link('http://example.com?a=b', 'example.com') . '&amp;c=d',
            'http://example.com?a=b&#38;c=d' => self::link('http://example.com?a=b', 'example.com') . '&#38;c=d',
            'http://example.com?a=b&amp;' => self::link('http://example.com?a=b', 'example.com') . '&amp;',
            '&lt;example.com&gt;' => '&lt;' . self::link('http://example.com', 'example.com') . '&gt;',
            'foo &amp; example.com' => 'foo &amp; ' . self::link('http://example.com', 'example.com'),
        ];

        foreach ($cases as $text => $expected) {
            $this->assertSame($expected, $urlLinker->linkUrlsInTrustedHtml($text));
        }
    }
}
