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

/**
 * Scans plain text for web and email addresses and classifies each
 * occurrence, yielding render-ready tokens.
 *
 * Every decision — regex building, TLD-validity check, ambiguous-TLD
 * suppression, email-vs-URL disambiguation, scheme defaulting and trailing
 * punctuation handling — lives in this module, so that "what counts as a
 * link" can be understood and tested in one place.
 *
 * @internal
 */
final class AddressScanner
{
    /**
     * Top-level domains that are also common file extensions. When the
     * "skip ambiguous TLDs" guard is enabled, a bare address (no scheme, no
     * username, no port/path/query/fragment) ending in one of these stays
     * plain text.
     *
     * @var list<string>
     */
    private const DEFAULT_AMBIGUOUS_TLDS = ['.zip', '.mov', '.java', '.ps', '.md', '.sh', '.py', '.rs', '.ai'];

    /**
     * @param array<string,bool> $validTlds An array of valid TLDs mapping each TLD to true.
     * @param array<string,bool> $ambiguousTlds An array of ambiguous TLDs mapping each TLD to true.
     */
    public function __construct(
        private readonly bool $allowFtpAddresses,
        private readonly bool $allowUpperCaseUrlSchemes,
        private readonly array $validTlds,
        private readonly bool $skipAmbiguousTlds,
        private readonly array $ambiguousTlds,
    ) {}

    /**
     * @return array<string,bool>
     */
    public static function getDefaultAmbiguousTlds(): array
    {
        return \array_fill_keys(self::DEFAULT_AMBIGUOUS_TLDS, true);
    }

    /**
     * Scan text and emit a stream of plain and address tokens.
     *
     * @param bool $decodeCharacterReferences Decode character references inside matched
     *                                        URLs before creating the tokens; false for
     *                                        `linkUrlsAndEscapeHtml()`, true for trusted HTML
     *
     * @return list<PlainToken|AddressToken>
     */
    public function scan(string $text, bool $decodeCharacterReferences = false): array
    {
        // We can abort if there is no . in $text
        if (!\str_contains($text, '.')) {
            return $text === '' ? [] : [new PlainToken($text)];
        }

        $tokens = [];

        $position = 0;

        $match = [];

        while (\preg_match($this->buildRegex(), $text, $match, PREG_OFFSET_CAPTURE, $position)) {
            [$url, $urlPosition] = $match[0];

            // Add the text leading up to the URL.
            $leading = \substr($text, $position, \intval($urlPosition - $position));

            if ($leading !== '') {
                $tokens[] = new PlainToken($leading);
            }

            $scheme      = $match['scheme'][0] ?? '';
            $username    = $match['username'][0] ?? '';
            $password    = $match['password'][0] ?? '';
            $domain      = $match['host'][0] ?? '';
            $afterDomain = $match['hostsuffix'][0] ?? ''; // everything following the domain
            $port        = $match['port'][0] ?? '';
            $path        = $match['path'][0] ?? '';

            $urlLength = \strlen($url);

            // A ';' that directly terminates a character reference (e.g. "&amp;",
            // "&#38;") belongs to the reference, not to trailing punctuation.
            if ($url !== ''
                && ($text[$urlPosition + $urlLength] ?? '') === ';'
                && \preg_match('{&(?:[a-zA-Z][a-zA-Z0-9]*|#[0-9]+|#[xX][0-9a-fA-F]+)$}', $url) === 1
            ) {
                $url .= ';';
                $urlLength++;
            }

            if ($decodeCharacterReferences) {
                // A character reference inside a URL stands for the character it names;
                // the link creators receive the decoded URL.
                $scheme   = $this->decodeCharacterReferences($scheme);
                $username = $this->decodeCharacterReferences($username);
                $password = $this->decodeCharacterReferences($password);
                $domain   = $this->decodeCharacterReferences($domain);
                $port     = $this->decodeCharacterReferences($port);
                $path     = $this->decodeCharacterReferences($path);
                $url      = $this->decodeCharacterReferences($url);
            }

            // A bare address ending in an ambiguous TLD stays plain text.
            if ($this->isSuppressedBareAddress($scheme, $username, $afterDomain, $domain)) {
                $tokens[] = new PlainToken($url);

                $position = $urlPosition + $urlLength;

                continue;
            }

            // Check that the TLD is valid or that $domain is an IP address.
            $tld = $this->lastHostLabel($domain);

            if (\preg_match('{^\.\d{1,3}$}', $tld) === 1 || isset($this->validTlds[$tld])) {
                // Do not permit implicit scheme if a password is specified, as
                // this causes too many errors (e.g. "my email:foo@example.org").
                if ($scheme === '' && $password !== '') {
                    $tokens[] = new PlainToken($username);

                    // Continue text parsing at the ':' following the "username".
                    $position = $urlPosition + \strlen($match['username'][0] ?? '');

                    continue;
                }

                $schemeIsEmpty = $scheme === '';
                $passwordIsEmpty = $password === '';

                if ($schemeIsEmpty && $username !== '' && $passwordIsEmpty && $afterDomain === '') {
                    // Looks like an email address.
                    $tokens[] = new AddressToken(true, $url, $url, $url);
                } else {
                    // Prepend http:// if no scheme is specified
                    $completeUrl = $scheme !== '' ? $url : 'http://' . $url;
                    $linkText = $domain . $port . $path;

                    $tokens[] = new AddressToken(false, $url, $completeUrl, $linkText);
                }
            } else {
                // Not a valid URL.
                $tokens[] = new PlainToken($url);
            }

            // Continue text parsing from after the URL.
            $position = $urlPosition + $urlLength;
        }

        // Add the remainder of the text.
        $remainder = \substr($text, $position);

        if ($remainder !== '') {
            $tokens[] = new PlainToken($remainder);
        }

        return $tokens;
    }

    /**
     * A match is suppressed exactly when the guard is enabled, the address is
     * bare (no scheme, no username, no port/path/query/fragment), and the
     * lowercased last label of the host is on the ambiguous TLD list.
     */
    private function isSuppressedBareAddress(string $scheme, string $username, string $afterDomain, string $domain): bool
    {
        if (! $this->skipAmbiguousTlds) {
            return false;
        }

        if ($scheme !== '' || $username !== '' || $afterDomain !== '') {
            return false;
        }

        return isset($this->ambiguousTlds[$this->lastHostLabel($domain)]);
    }

    private function lastHostLabel(string $host): string
    {
        return \strtolower((string) \strrchr($host, '.'));
    }

    /**
     * Decode character references inside a string.
     */
    public function decodeCharacterReferences(string $string): string
    {
        return \html_entity_decode($string, ENT_QUOTES | ENT_HTML401, $this->getEncoding());
    }

    private function getEncoding(): ?string
    {
        $encoding = \ini_get('default_charset');

        return $encoding !== false ? $encoding : null;
    }

    private function buildRegex(): string
    {
        /**
         * Regular expression bits used by scan() to match URLs.
         *
         * - password: allow the same characters as in the username
         * - trailpunct: valid URL characters which are not part of the URL if they appear at the very end
         * - nonurl: characters that should never appear in a URL
         */
        $rexScheme = 'https?://';

        if ($this->allowFtpAddresses) {
            $rexScheme .= '|ftp://';
        }

        $rexTrailPunct = "[)'?.!,;:]"; // valid URL characters which are not part of the URL if they appear at the very end
        $rexNonUrl	 = "[^-_\#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]"; // characters that should never appear in a URL

        $pcre = <<<PCRE
            #\\b
                (?P<scheme>{$rexScheme})?
                (?:
                    (?P<username>[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})
                    (?P<password>:[^]\\\\\\x00-\\x20\"(),:-<>[\\x7f-\\xff]{1,64})?
                @)?
                (?P<host>
                    (?:[-a-zA-Z0-9\\x7f-\\xff]{1,63}\.)+[a-zA-Z\\x7f-\\xff][-a-zA-Z0-9\\x7f-\\xff]{1,62}|
                    (?:[1-9]\d{0,2}\.|0\.){3}(?:[1-9]\d{0,2}|0)
                )
                (?P<hostsuffix>
                    (?P<port>:[0-9]{1,5})?
                    (?P<path>/[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]*?)?
                    (?P<query>\?[!$-/0-9:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?
                    (?P<fragment>\#[!$-/0-9?:;=@_':;!a-zA-Z\\x7f-\\xff]+?)?
                )
                (?={$rexTrailPunct}*
                    ({$rexNonUrl}|$)
                )
            #x
            PCRE
        ;

        if ($this->allowUpperCaseUrlSchemes) {
            $pcre .= 'i';
        }

        return $pcre;
    }
}
