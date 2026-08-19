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

namespace Art4\UrlLinker\Tests\Unit;

use Art4\UrlLinker\AddressScanner;
use Art4\UrlLinker\AddressToken;
use Art4\UrlLinker\DomainStorage;
use Art4\UrlLinker\PlainToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * An expected plain token: ['plain', text].
 * An expected address token: ['address', isEmail, url, completeUrl, linkText].
 *
 * @phpstan-type ExpectedToken array{0: 'plain', 1: string}|array{0: 'address', 1: bool, 2: string, 3: string, 4: string}
 * @phpstan-type ExpectedStream list<ExpectedToken>
 */
#[CoversClass(AddressScanner::class)]
#[CoversClass(AddressToken::class)]
#[CoversClass(PlainToken::class)]
final class AddressScannerTest extends TestCase
{
    public function testScanReturnsEmptyStreamForEmptyText(): void
    {
        $this->assertTokenStream($this->createScanner()->scan(''), []);
    }

    #[DataProvider('provideTextsWithoutDots')]
    public function testScanReturnsSinglePlainTokenForTextWithoutDots(string $text): void
    {
        $this->assertTokenStream($this->createScanner()->scan($text), [['plain', $text]]);
    }

    /**
     * @return \Iterator<string, array<int, string>>
     */
    public static function provideTextsWithoutDots(): \Iterator
    {
        yield 'empty-ish text' => ['Hello World!'];
        yield 'single word' => ['foo'];
        yield 'html markup' => ['<b>foo</b>'];
    }

    /**
     * @param ExpectedStream $expected
     */
    #[DataProvider('provideAddressScans')]
    public function testScanClassifiesAddressTokens(string $text, array $expected): void
    {
        $this->assertTokenStream($this->createScanner()->scan($text), $expected);
    }

    /**
     * @return \Iterator<string, array{string, ExpectedStream}>
     */
    public static function provideAddressScans(): \Iterator
    {
        yield 'bare domain' => [
            'example.com',
            [['address', false, 'example.com', 'http://example.com', 'example.com']],
        ];
        yield 'www domain' => [
            'www.example.com',
            [['address', false, 'www.example.com', 'http://www.example.com', 'www.example.com']],
        ];
        yield 'subdomain' => [
            'subdomain.example.com',
            [['address', false, 'subdomain.example.com', 'http://subdomain.example.com', 'subdomain.example.com']],
        ];
        yield 'explicit scheme' => [
            'https://example.com',
            [['address', false, 'https://example.com', 'https://example.com', 'example.com']],
        ];
        yield 'path' => [
            'e.com/subdir',
            [['address', false, 'e.com/subdir', 'http://e.com/subdir', 'e.com/subdir']],
        ];
        yield 'query is not part of the link text' => [
            'e.com?param1=val1',
            [['address', false, 'e.com?param1=val1', 'http://e.com?param1=val1', 'e.com']],
        ];
        yield 'fragment is not part of the link text' => [
            'e.com/test#hash',
            [['address', false, 'e.com/test#hash', 'http://e.com/test#hash', 'e.com/test']],
        ];
        yield 'ip address' => [
            '192.168.0.1',
            [['address', false, '192.168.0.1', 'http://192.168.0.1', '192.168.0.1']],
        ];
        yield 'email address' => [
            'mail@example.com',
            [['address', true, 'mail@example.com', 'mail@example.com', 'mail@example.com']],
        ];
        yield 'only link text keeps path and drops query' => [
            'e.com/subdir/resource.jpg?param1=val1&param2=val2',
            [[
                'address',
                false,
                'e.com/subdir/resource.jpg?param1=val1&param2=val2',
                'http://e.com/subdir/resource.jpg?param1=val1&param2=val2',
                'e.com/subdir/resource.jpg',
            ]],
        ];
        yield 'non-ascii path' => [
            'møøse.kwi.dk/foo',
            [['address', false, 'møøse.kwi.dk/foo', 'http://møøse.kwi.dk/foo', 'møøse.kwi.dk/foo']],
        ];
        yield 'multiple addresses split by plain text' => [
            'e1.com/t1 foo e2.com/t2',
            [
                ['address', false, 'e1.com/t1', 'http://e1.com/t1', 'e1.com/t1'],
                ['plain', ' foo '],
                ['address', false, 'e2.com/t2', 'http://e2.com/t2', 'e2.com/t2'],
            ],
        ];
    }

    /**
     * @param ExpectedStream $expected
     */
    #[DataProvider('provideTextsRejectedAsPlain')]
    public function testScanKeepsRejectedAddressesAsPlainText(string $text, array $expected): void
    {
        $this->assertTokenStream($this->createScanner()->scan($text), $expected);
    }

    /**
     * @return \Iterator<string, array{string, ExpectedStream}>
     */
    public static function provideTextsRejectedAsPlain(): \Iterator
    {
        yield 'invalid tld' => [
            'foo.unknown',
            [['plain', 'foo.unknown']],
        ];
        yield 'leading period is plain text' => [
            '.example.com',
            [
                ['plain', '.'],
                ['address', false, 'example.com', 'http://example.com', 'example.com'],
            ],
        ];
        yield 'trailing period is plain text' => [
            'Visit stackoverflow.com.',
            [
                ['plain', 'Visit '],
                ['address', false, 'stackoverflow.com', 'http://stackoverflow.com', 'stackoverflow.com'],
                ['plain', '.'],
            ],
        ];
        yield 'trailing punctuation run is plain text' => [
            'see example.com..',
            [
                ['plain', 'see '],
                ['address', false, 'example.com', 'http://example.com', 'example.com'],
                ['plain', '..'],
            ],
        ];
    }

    /**
     * @param ExpectedStream $expected
     */
    #[DataProvider('providePartialConsumptionScans')]
    public function testScanResumesAfterUsernameForImplicitSchemeWithPassword(string $text, array $expected): void
    {
        $this->assertTokenStream($this->createScanner()->scan($text), $expected);
    }

    /**
     * An implicit-scheme address carrying a password is rejected; parsing
     * resumes after the username, so the look-alike host can still link.
     *
     * @return \Iterator<string, array{string, ExpectedStream}>
     */
    public static function providePartialConsumptionScans(): \Iterator
    {
        yield 'implicit scheme with password' => [
            'x.y.z:secret@example.org',
            [
                ['plain', 'x.y.z'],
                ['plain', ':'],
                ['address', true, 'secret@example.org', 'secret@example.org', 'secret@example.org'],
            ],
        ];
    }

    public function testScanSuppressesBareAddressWithAmbiguousTld(): void
    {
        $scanner = $this->createScanner(true);

        $this->assertTokenStream($scanner->scan('foobar.zip'), [['plain', 'foobar.zip']]);
    }

    #[DataProvider('provideAmbiguousTldCases')]
    public function testScanSuppressesBareAddressesWithAmbiguousTld(string $text): void
    {
        $scanner = $this->createScanner(true);

        $this->assertTokenStream($scanner->scan($text), [['plain', $text]]);
    }

    /**
     * @return \Iterator<string, array<int, string>>
     */
    public static function provideAmbiguousTldCases(): \Iterator
    {
        yield 'zip extension' => ['foobar.zip'];
        yield 'mov extension' => ['clip.mov'];
        yield 'java extension' => ['Main.java'];
        yield 'ps extension' => ['script.ps'];
        yield 'md extension' => ['README.md'];
        yield 'sh extension' => ['install.sh'];
        yield 'py extension' => ['app.py'];
        yield 'rs extension' => ['main.rs'];
        yield 'ai extension' => ['vector.ai'];
        yield 'mixed case' => ['File.JAVA'];
        yield 'subdomain ending in ambiguous tld' => ['sub.foobar.zip'];
    }

    /**
     * @param ExpectedStream $expected
     */
    #[DataProvider('provideAddressesNotSuppressedByGuard')]
    public function testScanDoesNotSuppressAddressesWithSchemePathOrEmail(string $text, array $expected): void
    {
        $scanner = $this->createScanner(true);

        $this->assertTokenStream($scanner->scan($text), $expected);
    }

    /**
     * @return \Iterator<string, array{string, ExpectedStream}>
     */
    public static function provideAddressesNotSuppressedByGuard(): \Iterator
    {
        yield 'explicit scheme' => [
            'https://example.zip',
            [['address', false, 'https://example.zip', 'https://example.zip', 'example.zip']],
        ];
        yield 'with path' => [
            'dl.zip/file',
            [['address', false, 'dl.zip/file', 'http://dl.zip/file', 'dl.zip/file']],
        ];
        yield 'with port' => [
            'example.zip:8080',
            [['address', false, 'example.zip:8080', 'http://example.zip:8080', 'example.zip:8080']],
        ];
        yield 'email address' => [
            'user@example.zip',
            [['address', true, 'user@example.zip', 'user@example.zip', 'user@example.zip']],
        ];
        yield 'query is not part of the link text' => [
            'example.zip?x=1',
            [['address', false, 'example.zip?x=1', 'http://example.zip?x=1', 'example.zip']],
        ];
        yield 'fragment is not part of the link text' => [
            'example.zip#frag',
            [['address', false, 'example.zip#frag', 'http://example.zip#frag', 'example.zip']],
        ];
        yield 'genuine tld' => [
            'example.com',
            [['address', false, 'example.com', 'http://example.com', 'example.com']],
        ];
    }

    public function testScanDoesNotSuppressBareAddressWhenGuardIsOff(): void
    {
        $this->assertTokenStream(
            $this->createScanner(false)->scan('foobar.zip'),
            [['address', false, 'foobar.zip', 'http://foobar.zip', 'foobar.zip']]
        );
    }

    public function testScanClassifiesFtpAddressesWhenAllowed(): void
    {
        $scanner = $this->createScanner(allowFtpAddresses: true);

        $this->assertTokenStream(
            $scanner->scan('ftp://example.com'),
            [['address', false, 'ftp://example.com', 'ftp://example.com', 'example.com']]
        );
    }

    public function testScanClassifiesUppercaseSchemesWhenAllowed(): void
    {
        $scanner = $this->createScanner(allowUpperCaseUrlSchemes: true);

        $this->assertTokenStream(
            $scanner->scan('HTTP://EXAMPLE.COM'),
            [['address', false, 'HTTP://EXAMPLE.COM', 'HTTP://EXAMPLE.COM', 'EXAMPLE.COM']]
        );
    }

    public function testScanSuppressionWinsOverValidTldsWhitelist(): void
    {
        $scanner = $this->createScanner(true, ['.zip' => true]);

        $this->assertTokenStream($scanner->scan('foobar.zip'), [['plain', 'foobar.zip']]);
    }

    public function testScanSuppressesOnlyTldsFromOverriddenAmbiguousList(): void
    {
        $scanner = $this->createScanner(true, [], ['.zip' => true]);

        $this->assertTokenStream($scanner->scan('foobar.zip'), [['plain', 'foobar.zip']]);
        $this->assertTokenStream(
            $scanner->scan('app.py'),
            [['address', false, 'app.py', 'http://app.py', 'app.py']]
        );
    }

    public function testGetDefaultAmbiguousTldsContainsCommonFileExtensions(): void
    {
        $this->assertSame([
            '.zip' => true,
            '.mov' => true,
            '.java' => true,
            '.ps' => true,
            '.md' => true,
            '.sh' => true,
            '.py' => true,
            '.rs' => true,
            '.ai' => true,
        ], AddressScanner::getDefaultAmbiguousTlds());
    }

    public function testGetDefaultAmbiguousTldsExcludesGenuineTlds(): void
    {
        $ambiguousTlds = AddressScanner::getDefaultAmbiguousTlds();

        foreach (['.com', '.net', '.org', '.dev', '.app'] as $tld) {
            $this->assertArrayNotHasKey($tld, $ambiguousTlds);
        }
    }

    #[DataProvider('provideTrailingCharacterReferences')]
    public function testScanKeepsTrailingCharacterReferenceWhole(string $url): void
    {
        $this->assertTokenStream(
            $this->createScanner()->scan($url),
            [['address', false, $url, $url, 'example.com']]
        );
    }

    /**
     * @return \Iterator<string, array<int, string>>
     */
    public static function provideTrailingCharacterReferences(): \Iterator
    {
        yield 'named reference' => ['http://example.com?a=b&amp;'];
        yield 'decimal reference' => ['http://example.com?a=b&#38;'];
    }

    /**
     * @param ExpectedStream $expected
     */
    #[DataProvider('provideDecodedCharacterReferenceScans')]
    public function testScanDecodesCharacterReferences(string $text, array $expected): void
    {
        $this->assertTokenStream($this->createScanner()->scan($text, true), $expected);
    }

    /**
     * @return \Iterator<string, array{string, ExpectedStream}>
     */
    public static function provideDecodedCharacterReferenceScans(): \Iterator
    {
        yield 'named reference in query' => [
            'http://example.com?a=b&amp;c=d',
            [['address', false, 'http://example.com?a=b&c=d', 'http://example.com?a=b&c=d', 'example.com']],
        ];
        yield 'named reference in path' => [
            'e.com/foo&amp;bar',
            [['address', false, 'e.com/foo&bar', 'http://e.com/foo&bar', 'e.com/foo&bar']],
        ];
        yield 'decimal reference in query' => [
            'http://example.com?a=b&#38;c=d',
            [['address', false, 'http://example.com?a=b&c=d', 'http://example.com?a=b&c=d', 'example.com']],
        ];
    }

    public function testDecodeCharacterReferences(): void
    {
        $scanner = $this->createScanner();

        $this->assertSame('a&b', $scanner->decodeCharacterReferences('a&amp;b'));
        $this->assertSame('a&b', $scanner->decodeCharacterReferences('a&#38;b'));
        $this->assertSame('a<b', $scanner->decodeCharacterReferences('a&lt;b'));
    }

    /**
     * @param array<string,bool> $validTlds
     * @param array<string,bool> $ambiguousTlds
     */
    private function createScanner(bool $skipAmbiguousTlds = false, array $validTlds = [], array $ambiguousTlds = [], bool $allowFtpAddresses = false, bool $allowUpperCaseUrlSchemes = false): AddressScanner
    {
        return new AddressScanner(
            allowFtpAddresses: $allowFtpAddresses,
            allowUpperCaseUrlSchemes: $allowUpperCaseUrlSchemes,
            validTlds: $validTlds === [] ? DomainStorage::getValidTlds() : $validTlds,
            skipAmbiguousTlds: $skipAmbiguousTlds,
            ambiguousTlds: $ambiguousTlds === [] ? AddressScanner::getDefaultAmbiguousTlds() : $ambiguousTlds,
        );
    }

    /**
     * @param list<PlainToken|AddressToken> $tokens
     * @param ExpectedStream $expected
     */
    private function assertTokenStream(array $tokens, array $expected): void
    {
        $actual = [];

        foreach ($tokens as $token) {
            if ($token instanceof PlainToken) {
                $actual[] = ['plain', $token->text];

                continue;
            }

            $actual[] = ['address', $token->isEmail, $token->url, $token->completeUrl, $token->linkText];
        }

        $this->assertSame($expected, $actual);
    }
}
