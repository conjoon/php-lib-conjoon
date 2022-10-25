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

namespace Conjoon\Net;

/**
 * Internal class used by UriTemplate to provide regular expression for UriTemplates.
 *
 */
class UriTemplateRegex
{
    /**
     * @var string|null
     */
    private ?string $regex = null;

    /**
     * @var string
     */
    private string $templateString;


    /**
     * Constructor.
     *
     * @param string $templateString
     */
    final public function __construct(string $templateString)
    {
        $this->templateString = $templateString;
    }

    /**
     * @return string
     */
    public function getTemplateString(): string
    {
        return $this->templateString;
    }


    /**
     * Returns a regular expression built from this instance's $templateString.
     * The regular expression contains groups named after the path-parameters found in the string.
     *
     * @example
     *    // the following url-template "/Resources/{resourceId}" targets resources
     *    // identified with the path parameter "{resourceId}": "Resources/5" represents
     *    // a resource identified with "5" located at "Resources".
     *    // Additionally, it is assumed that "/Resources" represents a collection
     *    // of all available "Resource"-objects.
     *
     *    // $this->templateString = "/Resources/{resourceId}";
     *    $regex = $this->getRegexString();
     *    // $regex is now: "/\/Resources(\??[^\/]*$|\/(?<resourceId>[^\?\/]+))\??[^\/]*$/m"
     *    // which matches:
     *    //    - "Resources?fields[Resources]=4"
     *    //    - "Resources/24342"
     *    //    - "Resources/24342?include=CompoundResource"
     *    //    - "Resources"
     *    // ...
     *
     * @return string
     */
    public function getRegexString(): string
    {
        if ($this->regex) {
            return $this->regex;
        }

        $urlTemplate = $this->getTemplateString();

        $prepReg = "/{(.+?)}/m";
        $subst = '(?<$1>[^\?\/]+)';
        $input = str_replace(["/"], ["\\/"], $urlTemplate);

        // count the occurences and replace all except for last
        preg_match_all($prepReg, $input, $matches, PREG_SET_ORDER);

        $limit = max(0, count($matches) - 1);
        /** @var string $input*/
        $input = preg_replace($prepReg, $subst, $input, $limit);

        // apply final substitution
        $prepReg = "/(\\\\\/{(.+?)})/m";
        $subst = "(\??[^\/]*$|\/(?<$2>[^\?\/]+))\??[^\/]*$";
        $output = preg_replace($prepReg, $subst, $input);

        $this->regex = "/$output/m";

        return $this->regex;
    }


    /**
     * Tries to match the $url against the regular expression this instance represents.
     * Returns an array keyed with the names of all path parameters with their value
     * set to the extracted values.
     * Returns  null if the $url did not match the regular expression.
     *
     * @param string $url
     * @return array<string,string>|null
     *
     * @see getRegexString
     */
    public function match(string $url): ?array
    {
        $regex = $this->getRegexString();

        preg_match_all($regex, $url, $matches, PREG_SET_ORDER, 0);

        if (count($matches) === 0 || !is_array($matches[0])) {
            return null;
        }

        $matches = $matches[0];
        return array_filter($matches, fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
    }
}
