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

namespace Youthweb\UrlLinker\Tests\Unit\UrlLinker;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Youthweb\UrlLinker\UrlLinker;

#[CoversClass(UrlLinker::class)]
class BUildRegexTest extends TestCase
{
    /**
     * @dataProvider optionsRegexProvider
     *
     * @param array<string,mixed> $options
     */
    #[DataProvider('optionsRegexProvider')]
    public function testbuildRegexWithConfigReturnsCorrectRegex(array $options, string $expected): void
    {
        $urlLinker = new UrlLinker($options);

        $reflectionMethod = new \ReflectionMethod($urlLinker, 'buildRegex');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invoke($urlLinker));
    }

    /**
     * @return array<string,mixed>
     */
    public static function optionsRegexProvider(): array
    {
        return [
            'empty options' => [
                'options' => [],
                'expected' => <<<PRCE
                    {\b(https?://)?(?:([^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})(:[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})?@)?((?:[-a-zA-Z0-9\\x7f-\\xff]{1,63}\.)+[a-zA-Z\\x7f-\\xff][-a-zA-Z0-9\\x7f-\\xff]{1,62}|(?:[1-9]\d{0,2}\.|0\.){3}(?:[1-9]\d{0,2}|0))((:[0-9]{1,5})?(/[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]*?)?(\?[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?(#[!$-/0-9?:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?)(?=[)'?.!,;:]*([^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]|$))}
                    PRCE,
            ],
            'with ftp support' => [
                'options' => ['allowFtpAddresses' => true],
                'expected' => <<<PRCE
                    {\b(https?://|ftp://)?(?:([^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})(:[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})?@)?((?:[-a-zA-Z0-9\\x7f-\\xff]{1,63}\.)+[a-zA-Z\\x7f-\\xff][-a-zA-Z0-9\\x7f-\\xff]{1,62}|(?:[1-9]\d{0,2}\.|0\.){3}(?:[1-9]\d{0,2}|0))((:[0-9]{1,5})?(/[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]*?)?(\?[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?(#[!$-/0-9?:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?)(?=[)'?.!,;:]*([^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]|$))}
                    PRCE,
            ],
            'with uppercase support' => [
                'options' => ['allowUpperCaseUrlSchemes' => true],
                'expected' => <<<PRCE
                    {\b(https?://)?(?:([^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})(:[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})?@)?((?:[-a-zA-Z0-9\\x7f-\\xff]{1,63}\.)+[a-zA-Z\\x7f-\\xff][-a-zA-Z0-9\\x7f-\\xff]{1,62}|(?:[1-9]\d{0,2}\.|0\.){3}(?:[1-9]\d{0,2}|0))((:[0-9]{1,5})?(/[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]*?)?(\?[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?(#[!$-/0-9?:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?)(?=[)'?.!,;:]*([^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]|$))}i
                    PRCE,
            ],
            'full options' => [
                'options' => ['allowFtpAddresses' => true, 'allowUpperCaseUrlSchemes' => true],
                'expected' => <<<PRCE
                    {\b(https?://|ftp://)?(?:([^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})(:[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})?@)?((?:[-a-zA-Z0-9\\x7f-\\xff]{1,63}\.)+[a-zA-Z\\x7f-\\xff][-a-zA-Z0-9\\x7f-\\xff]{1,62}|(?:[1-9]\d{0,2}\.|0\.){3}(?:[1-9]\d{0,2}|0))((:[0-9]{1,5})?(/[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]*?)?(\?[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?(#[!$-/0-9?:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?)(?=[)'?.!,;:]*([^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]|$))}i
                    PRCE,
            ],
        ];
    }
}
