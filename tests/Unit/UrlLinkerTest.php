<?php

declare(strict_types=1);
/*
 * UrlLinker converts any web addresses in plain text into HTML hyperlinks.
 * Copyright (C) 2016-2022  Youthweb e.V. <info@youthweb.net>

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

use ArrayIterator;
use EmptyIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Youthweb\UrlLinker\UrlLinker;

class UrlLinkerTest extends TestCase
{
    /**
     * @test UrlLinker implements UrlLinkerInterface
     */
    public function testUrlLinkerImplementsUrlLinkerInterface(): void
    {
        $urlLinker = new UrlLinker();

        $this->assertInstanceOf('Youthweb\UrlLinker\UrlLinkerInterface', $urlLinker);
    }

    public function testProvidingClosureAsHtmlLinkCreator(): void
    {
        new UrlLinker([
            'htmlLinkCreator' => function (): void {},
        ]);

        // Workaround to test that NO Exception is thrown
        // @see https://github.com/sebastianbergmann/phpunit-documentation/issues/171
        $this->assertTrue(true);
    }

    /**
     * @dataProvider wrongCreatorProvider
     *
     * @param mixed $wrongCreator
     */
    public function testWrongHtmlLinkCreatorThrowsInvalidArgumentException($wrongCreator): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "htmlLinkCreator" must be of type "Closure", "');

        new UrlLinker([
            'htmlLinkCreator' => $wrongCreator,
        ]);
    }

    public function testProvidingClosureAsEmailLinkCreator(): void
    {
        new UrlLinker([
            'emailLinkCreator' => function (): void {},
        ]);

        // Workaround to test that NO Exception is thrown
        // @see https://github.com/sebastianbergmann/phpunit-documentation/issues/171
        $this->assertTrue(true);
    }

    /**
     * @dataProvider wrongCreatorProvider
     *
     * @param mixed $wrongCreator
     */
    public function testWrongEmailLinkCreatorThrowsInvalidArgumentException($wrongCreator): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "emailLinkCreator" must be of type "Closure", "');

        new UrlLinker([
            'emailLinkCreator' => $wrongCreator,
        ]);
    }

    public function testSettingValidTldsConfig(): void
    {
        new UrlLinker([
            'validTlds' => ['.com' => true, '.org' => true],
        ]);

        // Workaround to test that NO Exception is thrown
        // @see https://github.com/sebastianbergmann/phpunit-documentation/issues/171
        $this->assertTrue(
            true,
            'Return type ensures this assertion is never reached on failure'
        );
    }

    /**
     * Test that a closure can be set
     */
    public function testSettingHtmlLinkCreator(): void
    {
        // Simple htmlLinkCreator
        $creator = function ($url, $content) {
            return '<a href="' . $url . '">' . $content . '</a>';
        };

        $urlLinker = new UrlLinker([
            'htmlLinkCreator' => $creator,
        ]);

        // Workaround to test that NO Exception is thrown
        // @see https://github.com/sebastianbergmann/phpunit-documentation/issues/171
        $this->assertTrue(true);
    }

    /**
     * Test that a closure can be set
     */
    public function testSettingEmailLinkCreator(): void
    {
        // Simple emailLinkCreator
        $creator = function ($email, $content) {
            return '<a href="mailto:' . $email . '">' . $content . '</a>';
        };

        $urlLinker = new UrlLinker([
            'emailLinkCreator' => $creator,
        ]);

        // Workaround to test that NO Exception is thrown
        // @see https://github.com/sebastianbergmann/phpunit-documentation/issues/171
        $this->assertTrue(true);
    }

    public function testNotAllowingFtpAddresses(): void
    {
        $urlLinker = new UrlLinker([
            'allowFtpAddresses' => false,
            'validTlds' => ['.com' => true],
        ]);

        $text = '<div>ftp://example.com</div>';
        $expectedText = '&lt;div&gt;ftp://<a href="http://example.com">example.com</a>&lt;/div&gt;';

        $this->assertSame($expectedText, $urlLinker->linkUrlsAndEscapeHtml($text));

        $html = '<div>ftp://example.com</div>';
        $expectedHtml = '<div>ftp://<a href="http://example.com">example.com</a></div>';

        $this->assertSame($expectedHtml, $urlLinker->linkUrlsInTrustedHtml($html));
    }

    public function testAllowingFtpAddresses(): void
    {
        $urlLinker = new UrlLinker([
            'allowFtpAddresses' => true,
            'validTlds' => ['.com' => true],
        ]);

        $text = '<div>ftp://example.com</div>';
        $expectedText = '&lt;div&gt;<a href="ftp://example.com">example.com</a>&lt;/div&gt;';

        $this->assertSame($expectedText, $urlLinker->linkUrlsAndEscapeHtml($text));

        $html = '<div>ftp://example.com</div>';
        $expectedHtml = '<div><a href="ftp://example.com">example.com</a></div>';

        $this->assertSame($expectedHtml, $urlLinker->linkUrlsInTrustedHtml($html));
    }

    public function testProvidingAllowingFtpAddressesNotAsBooleanThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "allowFtpAddresses" must be of type "boolean", "string" given.');

        new UrlLinker([
            'allowFtpAddresses' => 'true',
        ]);
    }

    public function testNotAllowingUpperCaseSchemes(): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => false,
            'validTlds' => ['.com' => true],
        ]);

        $text = '<div>HTTP://example.com</div>';
        $expectedText = '&lt;div&gt;HTTP://<a href="http://example.com">example.com</a>&lt;/div&gt;';

        $this->assertSame($expectedText, $urlLinker->linkUrlsAndEscapeHtml($text));

        $html = '<div>HTTP://example.com</div>';
        $expectedHtml = '<div>HTTP://<a href="http://example.com">example.com</a></div>';

        $this->assertSame($expectedHtml, $urlLinker->linkUrlsInTrustedHtml($html));
    }

    public function testAllowingUpperCaseSchemes(): void
    {
        $urlLinker = new UrlLinker([
            'allowUpperCaseUrlSchemes' => true,
            'validTlds' => ['.com' => true],
        ]);

        $text = '<div>HTTP://example.com</div>';
        $expectedText = '&lt;div&gt;<a href="HTTP://example.com">example.com</a>&lt;/div&gt;';

        $this->assertSame($expectedText, $urlLinker->linkUrlsAndEscapeHtml($text));

        $html = '<div>HTTP://example.com</div>';
        $expectedHtml = '<div><a href="HTTP://example.com">example.com</a></div>';

        $this->assertSame($expectedHtml, $urlLinker->linkUrlsInTrustedHtml($html));
    }

    public function testProvidingAllowingUpperCaseSchemesNotAsBooleanThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "allowUpperCaseUrlSchemes" must be of type "boolean", "string" given.');

        new UrlLinker([
            'allowUpperCaseUrlSchemes' => 'true',
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    public static function wrongCreatorProvider(): array
    {
        return static::getAllExcept(['closure']);
    }

    /**
     * Retrieve an array in data provider format with a selection of all typical PHP data types
     * *except* the named types specified in the $except parameter.
     *
     * @see https://github.com/WordPress/Requests/pull/710
     *
     * @param string[] ...$except One or more arrays containing the names of the types to exclude.
     *
     * @return array<string,mixed>
     */
    private static function getAllExcept(array ...$except)
    {
        $except = array_flip(array_merge(...$except));

        return array_diff_key(static::getAll(), $except);
    }

    /**
     * Retrieve an array in data provider format with all typical PHP data types.
     *
     * @see https://github.com/WordPress/Requests/pull/710
     *
     * @return array<string, mixed>
     */
    private static function getAll()
    {
        return [
            'null' => [
                null,
            ],
            'boolean false' => [
                false,
            ],
            'boolean true' => [
                true,
            ],
            'integer 0' => [
                0,
            ],
            'negative integer' => [
                -123,
            ],
            'positive integer' => [
                786687,
            ],
            'float 0.0' => [
                0.0,
            ],
            'negative float' => [
                5.600e-3,
            ],
            'positive float' => [
                124.7,
            ],
            'empty string' => [
                '',
            ],
            'numeric string' => [
                '123',
            ],
            'textual string' => [
                'foobar',
            ],
            'textual string starting with numbers' => [
                '123 My Street',
            ],
            'empty array' => [
                [],
            ],
            'array with values, no keys' => [
                [1, 2, 3],
            ],
            'array with values, string keys' => [
                ['a' => 1, 'b' => 2],
            ],
            'callable as array with instanciated object' => [
                [__CLASS__, '__construct'],
            ],
            'closure' => [
                function () {
                    return true;
                },
            ],
            'plain object' => [
                new stdClass(),
            ],
            'ArrayIterator object' => [
                new ArrayIterator([1, 2, 3]),
            ],
            'Iterator object, no array access' => [
                new EmptyIterator(),
            ],
        ];
    }
}
