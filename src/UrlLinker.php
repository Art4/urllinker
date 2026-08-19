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

namespace Art4\UrlLinker;

use Closure;
use InvalidArgumentException;
use UnexpectedValueException;

final class UrlLinker implements UrlLinkerInterface
{
    /**
     * Ftp addresses like "ftp://example.com" will be allowed, default false
     */
    private bool $allowFtpAddresses = false;

    /**
     * Uppercase URL schemes like "HTTP://exmaple.com" will be allowed:
     */
    private bool $allowUpperCaseUrlSchemes = false;

    /**
     * Character references in trusted HTML cut URLs in the legacy way, default false.
     * With the default (false), character references that decode to a character that may
     * appear in a URL stay part of the URL; references to characters that may not are markup.
     */
    private bool $cutUrlsAtEntities = false;

    /**
     * Closure to modify the way the urls will be linked
     */
    private Closure $htmlLinkCreator;

    /**
     * Closure to modify the way the emails will be linked
     */
    private Closure $emailLinkCreator;

    /**
     * @var array<string,bool>
     */
    private array $validTlds;

    /**
     * Bare addresses ending in an ambiguous TLD will be skipped, default false
     */
    private bool $skipAmbiguousTlds = false;

    /**
     * @var array<string,bool>
     */
    private array $ambiguousTlds;

    private AddressScanner $scanner;

    /**
     * Set the configuration
     *
     * @param array<string,mixed> $options Configuation array
     */
    public function __construct(array $options = [])
    {
        $allowedOptions = [
            'allowFtpAddresses',
            'allowUpperCaseUrlSchemes',
            'cutUrlsAtEntities',
            'htmlLinkCreator',
            'emailLinkCreator',
            'validTlds',
            'skipAmbiguousTlds',
            'ambiguousTlds',
        ];

        foreach ($allowedOptions as $allowedOption) {
            switch ($allowedOption) {
                case 'allowFtpAddresses':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_bool($value)) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                'boolean',
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = false;
                    }

                    $this->allowFtpAddresses = $value;

                    break;

                case 'allowUpperCaseUrlSchemes':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_bool($value)) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                'boolean',
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = false;
                    }

                    $this->allowUpperCaseUrlSchemes = $value;

                    break;

                case 'cutUrlsAtEntities':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_bool($value)) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                'boolean',
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = false;
                    }

                    $this->cutUrlsAtEntities = $value;

                    break;

                case 'htmlLinkCreator':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_object($value) || ! $value instanceof Closure) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                Closure::class,
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = $this->createHtmlLink(...);
                    }

                    $this->htmlLinkCreator = $value;

                    break;

                case 'emailLinkCreator':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_object($value) || ! $value instanceof Closure) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                Closure::class,
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = $this->createEmailLink(...);
                    }

                    $this->emailLinkCreator = $value;

                    break;

                case 'validTlds':
                    $value = \array_key_exists($allowedOption, $options) ? (array) $options[$allowedOption] : DomainStorage::getValidTlds();

                    $validTlds = [];

                    foreach ($value as $tld => $flag) {
                        $validTlds[(string) $tld] = (bool) $flag;
                    }

                    $this->validTlds = $validTlds;

                    break;

                case 'skipAmbiguousTlds':
                    if (\array_key_exists($allowedOption, $options)) {
                        $value = $options[$allowedOption];

                        if (! \is_bool($value)) {
                            throw new InvalidArgumentException(\sprintf(
                                'Option "%s" must be of type "%s", "%s" given.',
                                $allowedOption,
                                'boolean',
                                \get_debug_type($value)
                            ));
                        }
                    } else {
                        $value = false;
                    }

                    $this->skipAmbiguousTlds = $value;

                    break;

                case 'ambiguousTlds':
                    $value = \array_key_exists($allowedOption, $options) ? (array) $options[$allowedOption] : AddressScanner::getDefaultAmbiguousTlds();

                    $ambiguousTlds = [];

                    foreach ($value as $tld => $flag) {
                        $ambiguousTlds[(string) $tld] = (bool) $flag;
                    }

                    $this->ambiguousTlds = $ambiguousTlds;

                    break;
            }
        }

        $this->scanner = new AddressScanner(
            allowFtpAddresses: $this->allowFtpAddresses,
            allowUpperCaseUrlSchemes: $this->allowUpperCaseUrlSchemes,
            validTlds: $this->validTlds,
            skipAmbiguousTlds: $this->skipAmbiguousTlds,
            ambiguousTlds: $this->ambiguousTlds,
        );
    }

    public function linkUrlsAndEscapeHtml(string $text): string
    {
        return $this->linkUrlsInPlainText($text, false);
    }

    /**
     * Link URLs inside plain text.
     *
     * @param bool $decodeCharacterReferences Decode character references inside matched
     *                                        URLs before creating the link; false for
     *                                        `linkUrlsAndEscapeHtml()`, true for trusted HTML
     */
    private function linkUrlsInPlainText(string $text, bool $decodeCharacterReferences): string
    {
        $html = '';

        foreach ($this->scanner->scan($text, $decodeCharacterReferences) as $token) {
            if ($token instanceof PlainToken) {
                // Escape the whole plain token, including any leading periods.
                $html .= $this->escapeHtml($token->text);

                continue;
            }

            if ($token->isEmail) {
                $link = $this->emailLinkCreator->__invoke($token->url, $token->linkText);
                $creatorName = 'emailLinkCreator';
            } else {
                $link = $this->htmlLinkCreator->__invoke($token->completeUrl, $token->linkText);
                $creatorName = 'htmlLinkCreator';
            }

            if (! \is_string($link)) {
                throw new UnexpectedValueException(\sprintf(
                    'Return value of Closure for "%s" must return value of type "string", "%s" given.',
                    $creatorName,
                    \gettype($link)
                ));
            }

            $html .= $link;
        }

        return $html;
    }

    public function linkUrlsInTrustedHtml(string $html): string
    {
        $reMarkup = '{</?([a-z]+)([^"\'>]|"[^"]*"|\'[^\']*\')*>|&#?[a-zA-Z0-9]+;|$}';

        $insideAnchorTag = false;
        $position = 0;
        $result = '';

        // Iterate over every piece of markup in the HTML.
        while (true) {
            $textStart = $position;

            // Find the next markup that is not part of a URL: a tag, a character
            // reference to a character that cannot appear in a URL, or the end.
            while (true) {
                $match = [];

                if (\preg_match($reMarkup, $html, $match, PREG_OFFSET_CAPTURE, $position) !== 1) {
                    // Unreachable: $reMarkup ends in "|$", so the regex always matches
                    // (the empty string at the end of $html). Kept only as a guard.
                    // @codeCoverageIgnoreStart
                    $position = \strlen($html);
                    $markup = '';
                    $markupPosition = \strlen($html);

                    break;
                    // @codeCoverageIgnoreEnd
                }

                [$markup, $markupPosition] = $match[0];

                // A character reference to a character that may appear in a URL
                // belongs to the text and never splits a URL.
                if (! $this->cutUrlsAtEntities && $markup !== '' && $markup[0] === '&' && ! $this->decodesToNonUrlCharacter($markup)) {
                    $position = $markupPosition + \strlen($markup);

                    continue;
                }

                break;
            }

            // Process text leading up to the markup.
            $text = \substr($html, $textStart, $markupPosition - $textStart);

            // Link URLs unless we're inside an anchor tag.
            if (! $insideAnchorTag) {
                $text = $this->linkUrlsInPlainText($text, ! $this->cutUrlsAtEntities);
            }

            $result .= $text;

            // End of HTML?
            if ($markup === '') {
                break;
            }

            // Check if markup is an anchor tag ('<a>', '</a>').
            if ($markup[0] !== '&' && isset($match[1]) && $match[1][0] === 'a') {
                $insideAnchorTag = ($markup[1] !== '/');
            }

            // Pass markup through unchanged.
            $result .= $markup;

            // Continue after the markup.
            $position = $markupPosition + \strlen($markup);
        }

        return $result;
    }

    /**
     * Default method for creating a HTML link
     */
    private function createHtmlLink(string $url, string $content): string
    {
        $link = \sprintf(
            '<a href="%s">%s</a>',
            $this->escapeHtml($url),
            $this->escapeHtml($content)
        );

        // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
        return \str_replace('@', '&#64;', $link);
    }

    /**
     * Default method for creating an email link
     */
    private function createEmailLink(string $url, string $content): string
    {
        $link = $this->createHtmlLink('mailto:' . $url, $content);

        // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
        return \str_replace('@', '&#64;', $link);
    }

    private function escapeHtml(string $string): string
    {
        $flags = ENT_COMPAT | ENT_HTML401;
        $double_encode = false; // Do not double encode

        return \htmlspecialchars($string, $flags, $this->getEncoding(), $double_encode);
    }

    private function getEncoding(): ?string
    {
        $encoding = \ini_get('default_charset');

        return $encoding !== false ? $encoding : null;
    }

    private function decodesToNonUrlCharacter(string $characterReference): bool
    {
        $decoded = $this->scanner->decodeCharacterReferences($characterReference);

        return $decoded !== '' && \preg_match('{^[\x00-\x20\p{Zs}"<>]+$}u', $decoded) === 1;
    }
}
