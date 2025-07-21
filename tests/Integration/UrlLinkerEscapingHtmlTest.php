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

namespace Youthweb\UrlLinker\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Youthweb\UrlLinker\UrlLinker;

#[CoversMethod(UrlLinker::class, 'escapeHtml')]
#[CoversMethod(UrlLinker::class, 'linkUrlsAndEscapeHtml')]
class UrlLinkerEscapingHtmlTest extends UrlLinkerTestCase
{
    /**
     * @dataProvider provideTextsWithFtpLinksWithoutHtml
     */
    #[DataProvider('provideTextsWithFtpLinksWithoutHtml')]
    public function testFtpUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'allowFtpAddresses' => true,
        ]);

        $this->runLinkUrlsAndEscapeHtmlTests($urlLinker, $text, $expectedLinked, $message);
    }

    /**
     * @dataProvider provideTextsWithUppercaseLinksWithoutHtml
     */
    #[DataProvider('provideTextsWithUppercaseLinksWithoutHtml')]
    public function testUppercaseUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => true,
        ]);

        $this->runLinkUrlsAndEscapeHtmlTests($urlLinker, $text, $expectedLinked, $message);
    }

    /**
     * @dataProvider provideTextsNotContainingAnyUrls
     */
    #[DataProvider('provideTextsNotContainingAnyUrls')]
    public function testTextNotContainingAnyUrlsRemainsTheSame(string $text): void
    {
        $this->assertSame($text, (new UrlLinker())->linkUrlsAndEscapeHtml($text));
    }

    public function testExample(): void
    {
        $text = <<<EOD
            Here's an e-mail-address:bob+test@example.org. Here's an authenticated URL: http://skroob:12345@example.com.
            Here are some URLs:
            stackoverflow.com/questions/1188129/pregreplace-to-detect-html-php
            Here's the answer: http://www.google.com/search?rls=en&q=42&ie=utf-8&oe=utf-8&hl=en. What was the question?
            A quick look at 'http://en.wikipedia.org/wiki/URI_scheme#Generic_syntax' is helpful.
            There is no place like 127.0.0.1! Except maybe http://news.bbc.co.uk/1/hi/england/surrey/8168892.stm?
            Ports: 192.168.0.1:8080, https://example.net:1234/.
            Beware of Greeks bringing internationalized top-level domains (xn--hxajbheg2az3al.xn--jxalpdlp).
            10.000.000.000 is not an IP-address. Nor is this.a.domain.

            <script>alert('Remember kids: Say no to XSS-attacks! Always HTML escape untrusted input!');</script>

            https://mail.google.com/mail/u/0/#starred?compose=141d598cd6e13025
            https://www.google.com/search?q=bla%20bla%20bla
            https://www.google.com/search?q=bla+bla+bla

            We need to support IDNs and IRIs and röck döts:
            møøse.kwi.dk/阿驼鹿一旦咬了我的妹妹/من-اليمين-إلى-اليسار-لغات-تخلط-لي.
            EOD;

        $expected = <<<EOD
            Here's an e-mail-address:<a href="mailto:bob+test&#64;example.org">bob+test&#64;example.org</a>. Here's an authenticated URL: <a href="http://skroob:12345&#64;example.com">example.com</a>.
            Here are some URLs:
            <a href="http://stackoverflow.com/questions/1188129/pregreplace-to-detect-html-php">stackoverflow.com/questions/1188129/pregreplace-to-detect-html-php</a>
            Here's the answer: <a href="http://www.google.com/search?rls=en&amp;q=42&amp;ie=utf-8&amp;oe=utf-8&amp;hl=en">www.google.com/search</a>. What was the question?
            A quick look at '<a href="http://en.wikipedia.org/wiki/URI_scheme#Generic_syntax">en.wikipedia.org/wiki/URI_scheme</a>' is helpful.
            There is no place like <a href="http://127.0.0.1">127.0.0.1</a>! Except maybe <a href="http://news.bbc.co.uk/1/hi/england/surrey/8168892.stm">news.bbc.co.uk/1/hi/england/surrey/8168892.stm</a>?
            Ports: <a href="http://192.168.0.1:8080">192.168.0.1:8080</a>, <a href="https://example.net:1234/">example.net:1234/</a>.
            Beware of Greeks bringing internationalized top-level domains (xn--hxajbheg2az3al.xn--jxalpdlp).
            10.000.000.000 is not an IP-address. Nor is this.a.domain.

            &lt;script&gt;alert('Remember kids: Say no to XSS-attacks! Always HTML escape untrusted input!');&lt;/script&gt;

            <a href="https://mail.google.com/mail/u/0/#starred?compose=141d598cd6e13025">mail.google.com/mail/u/0/</a>
            <a href="https://www.google.com/search?q=bla%20bla%20bla">www.google.com/search</a>
            <a href="https://www.google.com/search?q=bla+bla+bla">www.google.com/search</a>

            We need to support IDNs and IRIs and röck döts:
            <a href="http://møøse.kwi.dk/阿驼鹿一旦咬了我的妹妹/من-اليمين-إلى-اليسار-لغات-تخلط-لي">møøse.kwi.dk/阿驼鹿一旦咬了我的妹妹/من-اليمين-إلى-اليسار-لغات-تخلط-لي</a>.
            EOD;

        $this->assertSame($expected, (new UrlLinker())->linkUrlsAndEscapeHtml($text));
    }

    /**
     * @dataProvider provideTextsWithLinksWithoutHtml
     */
    #[DataProvider('provideTextsWithLinksWithoutHtml')]
    public function testUrlsGetLinkedInText(string $text, string $expectedLinked, ?string $message = null): void
    {
        $this->runLinkUrlsAndEscapeHtmlTests(new UrlLinker(), $text, $expectedLinked, $message);
    }

    private function runLinkUrlsAndEscapeHtmlTests(UrlLinker $urlLinker, string $text, string $expectedLinked, ?string $message = null): void
    {
        $this->assertSame(
            $expectedLinked,
            $urlLinker->linkUrlsAndEscapeHtml($text),
            'Simple case: ' . $message
        );

        $this->assertSame(
            sprintf('foo %s bar', $expectedLinked),
            $urlLinker->linkUrlsAndEscapeHtml(sprintf('foo %s bar', $text)),
            'Text around: ' . $message
        );

        // html should get encoded
        $this->assertSame(
            sprintf('&lt;div class=&quot;test&quot;&gt; %s &lt;/div&gt;', $expectedLinked),
            $urlLinker->linkUrlsAndEscapeHtml(sprintf('<div class="test"> %s </div>', $text)),
            'Html around: ' . $message
        );
    }

    /**
     * @dataProvider provideTextsWithHtml
     */
    #[DataProvider('provideTextsWithHtml')]
    public function testHtmlInText(string $text, string $expectedLinked): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => true,
        ]);

        $this->runLinkUrlsAndEscapeHtmlTests($urlLinker, $text, $expectedLinked);
    }

    /**
     * provide html in text
     *
     * @return array<int,array<int,string>>
     */
    public static function provideTextsWithHtml(): array
    {
        return [
            [
                'http://example.com?a=b&c=d',
                static::link('http://example.com?a=b&amp;c=d', 'example.com'),
            ],
            [
                'http://example.com?a=b&amp%3bc=d',
                static::link('http://example.com?a=b&amp;amp%3bc=d', 'example.com'),
            ],
            [
                'http://example.com?a=b&amp;c=d',
                static::link('http://example.com?a=b&amp;c=d', 'example.com'),
            ],
        ];
    }
}
