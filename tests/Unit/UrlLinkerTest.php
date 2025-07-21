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
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Youthweb\UrlLinker\UrlLinker;
use Youthweb\UrlLinker\UrlLinkerInterface;

#[CoversClass(UrlLinker::class)]
class UrlLinkerTest extends TestCase
{
    public function testUrlLinkerImplementsUrlLinkerInterface(): void
    {
        $urlLinker = new UrlLinker();

        $this->assertInstanceOf(UrlLinkerInterface::class, $urlLinker);
    }

    public function testProvidingClosureAsHtmlLinkCreator(): void
    {
        $urlLinker = new UrlLinker([
            'htmlLinkCreator' => function (): void {
                throw new Exception('it works');
            },
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('it works');

        $urlLinker->linkUrlsAndEscapeHtml('http://example.com');
    }

    /**
     * @dataProvider wrongCreatorProvider
     *
     * @param mixed $wrongCreator
     */
    #[DataProvider('wrongCreatorProvider')]
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
        $urlLinker = new UrlLinker([
            'emailLinkCreator' => function (): void {
                throw new Exception('it works');
            },
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('it works');

        $urlLinker->linkUrlsAndEscapeHtml('mail@example.com');
    }

    /**
     * @dataProvider wrongCreatorProvider
     *
     * @param mixed $wrongCreator
     */
    #[DataProvider('wrongCreatorProvider')]
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
        $urlLinker = new UrlLinker([
            'validTlds' => ['.com' => true, '.org' => true],
        ]);

        $this->assertSame(
            'Replace <a href="http://example.com">example.com</a> but not http://example.net',
            $urlLinker->linkUrlsAndEscapeHtml('Replace http://example.com but not http://example.net')
        );
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
        return self::getAllExcept(['closure']);
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
    private static function getAllExcept(array ...$except): array
    {
        $except = array_flip(array_merge(...$except));

        return array_diff_key(self::getAll(), $except);
    }

    /**
     * Retrieve an array in data provider format with all typical PHP data types.
     *
     * @see https://github.com/WordPress/Requests/pull/710
     *
     * @return array<string, mixed>
     */
    private static function getAll(): array
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
                [self::class, '__construct'],
            ],
            'closure' => [
                fn(): bool => true,
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
