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
     * Character references in trusted HTML cut URLs in the legacy way, default false.
     * With the default (false), character references that decode to a character that may
     * appear in a URL stay part of the URL; references to characters that may not are markup.
     */
    private readonly bool $cutUrlsAtEntities;

    /**
     * Closure to modify the way the urls will be linked
     */
    private readonly Closure $htmlLinkCreator;

    /**
     * Closure to modify the way the emails will be linked
     */
    private readonly Closure $emailLinkCreator;

    private readonly AddressScanner $scanner;

    /**
     * Set the configuration
     *
     * @param array<string,mixed> $options Configuation array
     */
    public function __construct(array $options = [])
    {
        // The options are checked in this order, so with several invalid
        // options the first one here throws.
        $allowFtpAddresses = $this->boolOption($options, 'allowFtpAddresses');
        $allowUpperCaseUrlSchemes = $this->boolOption($options, 'allowUpperCaseUrlSchemes');
        $this->cutUrlsAtEntities = $this->boolOption($options, 'cutUrlsAtEntities');
        $this->htmlLinkCreator = $this->closureOption($options, 'htmlLinkCreator') ?? $this->createHtmlLink(...);
        $this->emailLinkCreator = $this->closureOption($options, 'emailLinkCreator') ?? $this->createEmailLink(...);
        $validTlds = $this->tldListOption($options, 'validTlds', DomainStorage::getValidTlds(...));
        $skipAmbiguousTlds = $this->boolOption($options, 'skipAmbiguousTlds');
        $ambiguousTlds = $this->tldListOption($options, 'ambiguousTlds', AddressScanner::getDefaultAmbiguousTlds(...));

        $this->scanner = new AddressScanner(
            allowFtpAddresses: $allowFtpAddresses,
            allowUpperCaseUrlSchemes: $allowUpperCaseUrlSchemes,
            validTlds: $validTlds,
            skipAmbiguousTlds: $skipAmbiguousTlds,
            ambiguousTlds: $ambiguousTlds,
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    private function boolOption(array $options, string $name): bool
    {
        if (! \array_key_exists($name, $options)) {
            return false;
        }

        $value = $options[$name];

        if (! \is_bool($value)) {
            throw new InvalidArgumentException(\sprintf(
                'Option "%s" must be of type "%s", "%s" given.',
                $name,
                'boolean',
                \get_debug_type($value)
            ));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $options
     */
    private function closureOption(array $options, string $name): ?Closure
    {
        if (! \array_key_exists($name, $options)) {
            return null;
        }

        $value = $options[$name];

        if (! $value instanceof Closure) {
            throw new InvalidArgumentException(\sprintf(
                'Option "%s" must be of type "%s", "%s" given.',
                $name,
                Closure::class,
                \get_debug_type($value)
            ));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $options
     * @param Closure(): array<string,bool> $default Called only when the option is missing
     *
     * @return array<string,bool>
     */
    private function tldListOption(array $options, string $name, Closure $default): array
    {
        $value = \array_key_exists($name, $options) ? (array) $options[$name] : $default();

        $tlds = [];

        foreach ($value as $tld => $flag) {
            $tlds[(string) $tld] = (bool) $flag;
        }

        return $tlds;
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
                $html .= $this->checkedLink(
                    'emailLinkCreator',
                    $this->emailLinkCreator->__invoke($token->url, $token->linkText)
                );

                continue;
            }

            $html .= $this->checkedLink(
                'htmlLinkCreator',
                $this->htmlLinkCreator->__invoke($token->completeUrl, $token->linkText)
            );
        }

        return $html;
    }

    private function checkedLink(string $creatorName, mixed $link): string
    {
        if (! \is_string($link)) {
            throw new UnexpectedValueException(\sprintf(
                'Return value of Closure for "%s" must return value of type "string", "%s" given.',
                $creatorName,
                \gettype($link)
            ));
        }

        return $link;
    }

    public function linkUrlsInTrustedHtml(string $html): string
    {
        $insideAnchorTag = false;
        $position = 0;
        $result = '';

        // Iterate over every piece of markup in the HTML.
        while (true) {
            [$markup, $markupPosition, $tagName] = $this->findNextMarkup($html, $position);

            // Process text leading up to the markup.
            $text = \substr($html, $position, $markupPosition - $position);

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
            if ($tagName === 'a') {
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
     * Find the next markup that is not part of a URL: a tag, a character
     * reference to a character that cannot appear in a URL, or the end.
     *
     * @return array{string, int, string} The markup (empty at the end of the HTML), its
     *                                    position and, for a tag, its name; otherwise ''
     */
    private function findNextMarkup(string $html, int $position): array
    {
        $reMarkup = '{</?([a-z]+)([^"\'>]|"[^"]*"|\'[^\']*\')*>|&#?[a-zA-Z0-9]+;|$}';

        while (true) {
            $match = [];

            if (\preg_match($reMarkup, $html, $match, PREG_OFFSET_CAPTURE, $position) !== 1) {
                // Unreachable: $reMarkup ends in "|$", so the regex always matches
                // (the empty string at the end of $html). Kept only as a guard.
                // @codeCoverageIgnoreStart
                return ['', \strlen($html), ''];
                // @codeCoverageIgnoreEnd
            }

            [$markup, $markupPosition] = $match[0];

            // A character reference to a character that may appear in a URL
            // belongs to the text and never splits a URL.
            if (! $this->cutUrlsAtEntities && $markup !== '' && $markup[0] === '&' && ! $this->decodesToNonUrlCharacter($markup)) {
                $position = $markupPosition + \strlen($markup);

                continue;
            }

            return [$markup, $markupPosition, isset($match[1]) ? $match[1][0] : ''];
        }
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
