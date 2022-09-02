<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Conjoon\JsonApi\Request;

/**
 * Helper function for URL based operations requiring regular expressions for determining the
 * targeted resource of a request.
 *
 * @example
 *    $regex = new UrlRegex("/MailAccounts\/.+\/MailFolders\/.*\/(MessageItems)(\/)*$/m", 1, 2);
 *
 *    $regex->isSingleRequest("http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageItems/123"); // true
 *    $regex->getResourceName("http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageItems/123"); // "MessageItem"
 *
 *    $regex->isSingleRequest("http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageBodies"); // false
 *    $regex->getResourceName("http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageBodies"); // "MessageBody"
 *
 *
 *   $locator = new UrlMatcherLocator("App\\Query\\Validation", $matchers);
 *
 */
class UrlRegex
{
    /**
     * @var int
     */
    private int $singleIndex;


    /**
     * @var int
     */
    private int $nameIndex;


    /**
     * @var string
     */
    private string $regex;


    /**
     * Constructor.
     *
     * @param string $regex The regular expression.
     * @param int $nameIndex The group index in the regular expression that captures the targeted resource name.
     * @param int $singleIndex The group index in the regular expression that captures additional characters hinting
     * to a single resource request. If not available, the tested url should be treated as a collection request.
     *
     */
    public function __construct(string $regex, int $nameIndex, int $singleIndex)
    {
        $this->regex = $regex;
        $this->nameIndex = $nameIndex;
        $this->singleIndex = $singleIndex;
    }


    /**
     * Returns true if the $url requests a single resource object, false if it targets a collection.
     * If the $url passed was not matched by THIS regex, null is returned.
     *
     * @param string $url
     *
     * @return bool|null
     */
    public function isSingleRequest(string $url): ?bool
    {
        preg_match_all($this->regex, $url, $matches, PREG_SET_ORDER, 0);

        if ($matches) {
            if (isset($matches[0][$this->getSingleIndex()]) && $matches[0][$this->getSingleIndex()] != "") {
                return true;
            }
            return false;
        }

        return null;
    }


    /**
     * Returns the name of the resource object targeted by the specified url. Returns null if none was found.
     *
     * @param string $url
     *
     * @return string|null
     */
    public function getResourceName(string $url): ?string
    {
        preg_match_all($this->regex, $url, $matches, PREG_SET_ORDER, 0);

        if ($matches) {
            return $this->normalizeName($matches[0][$this->nameIndex]);
        }

        return null;
    }


    /**
     * Normalizes the resource target's name, if required.
     * Will check if any pluralized  name was found and transform the name to its singular version.
     *
     * @example
     *    $this->normalizeResourceTarget("MessageBodies"); // "MessageBody"
     *    $this->normalizeResourceTarget("MessageItems"); // "MessageItem"
     *    $this->normalizeResourceTarget("Addresses"); // "Address"
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        if (str_ends_with($name, "ies")) {
            return substr($name, 0, -3) . "y";
        }

        if (str_ends_with($name, "es")) {
            return substr($name, 0, -2);
        }

        if (str_ends_with($name, "s")) {
            return substr($name, 0, -1);
        }

        return $name;
    }


    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }


    /**
     * @return int
     */
    public function getSingleIndex(): int
    {
        return $this->singleIndex;
    }


    /**
     * @return int
     */
    public function getNameIndex(): int
    {
        return $this->nameIndex;
    }
}
