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

namespace Conjoon\Rest\Request;

use Conjoon\Core\Contract\Arrayable;

/**
 * Provides functionality to map urls to url-templates, for retrieving information about
 * associated resource targets and path parameters.
 *
 * @example
 *    $regex = new ResourceUrlRegex("/MailFolders/{mailFolderId}/MessageItems/{messageItemId}", MessageItem::class);
 *    $regex->getPathParameterNames();
 *    // ["mailFolderId", "messageItemId"];
 *
 *    $regex->hasResourceId("http://localhost/MailFolders/INBOX/MessageItems/123"); // true
 *    $regex->getResourceName("http://localhost/MailFolders/INBOX/MessageItems/123"); // "MessageItem"
 *
 *    $regex->getParameters("http://localhost/MailFolders/INBOX/MessageItems/123");
 *    // ["mailFolderId" => "INBOX", "messageItemId" => "123"]
 */
class ResourceUrlRegex implements Arrayable
{
    /**
     * @var ?string
     */
    private ?string $regex = null;

    /**
     * @var string
     */
    private readonly string $urlTemplate;

    /**
     * @var string
     */
    private readonly string $resourceName;


    /**
     * @var array<int, string>
     */
    private array $pathParameterNames = [];


    /**
     * Constructor.
     *
     * @param string $urlTemplate
     * @param string $resourceName
     */
    public function __construct(string $urlTemplate, string $resourceName)
    {
        $this->urlTemplate = $urlTemplate;
        $this->resourceName = $resourceName;
    }


    /**
     * Converts this instance's $urlTemplate to a regular expression.
     * The regular expression contains groups named after the path-parameters found in the string.
     *
     * @example
     *    // the following url-fragment "/Resources/{resourceId}" targets resources
     *    // identified with the path parameter "{resourceId}": "Resources/5" represents
     *    // an "Resource"-object with the id "5".
     *    // Additionally, it is assumed that "/Resources" represents a collection
     *    // of all available "Resource"-objects.
     *
     *    // $this->urlTemplate = "/Resources/{resourceId}";
     *    $output = $this->prepareRegex();
     *    // $output is now: "/\/Resources(\??[^\/]*$|\/(?<resourceId>[^\?\/]+))\??[^\/]*$/m"
     *    // which matches:
     *    //    - "Resources?fields[Resources]=4"
     *    //    - "Resources/24342"
     *    //    - "Resources/24342?include=CompoundResource"
     *    //    - "Resources"
     *    // ...
     *
     * @return string
     */
    protected function prepareRegex(): string
    {
        if ($this->regex) {
            return $this->regex;
        }

        $urlTemplate = $this->getUrlTemplate();

        $prepReg = "/\{(.+?)\}/m";
        $subst = '(?<$1>[^\?\/]+)';
        $input = str_replace(["/"], ["\\/"], $urlTemplate);

        // count the occurences and replace all except for last
        preg_match_all($prepReg, $input, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $this->pathParameterNames[] = $match[1];
        }

        $limit = max(0, count($matches) - 1);
        /** @var string $input*/
        $input = preg_replace($prepReg, $subst, $input, $limit);

        // apply final substitution
        $prepReg = "/(\\\\\/\{(.+?)\})/m";
        $subst = "(\??[^\/]*$|\/(?<$2>[^\?\/]+))\??[^\/]*$";
        $output = preg_replace($prepReg, $subst, $input);

        $this->regex = "/$output/m";

        return $this->regex;
    }


    /**
     * Returns the regular expression that was computed based on the information available with
     * $this->urlTemplate.
     *
     * @return string
     *
     * @see prepareRegex
     */
    public function getRegexString(): string
    {
        return $this->prepareRegex();
    }


    /**
     * Returns true if the $url requests a single resource object, false if it targets a collection.
     * If the submitted $url was not matched by THIS url-regex, null is returned.
     * If the $url does not match THIS instance's single-index pattern, false will be returned.
     *
     * @param string $url
     *
     * @return bool|null
     */
    public function hasResourceId(string $url): ?bool
    {
        $matches = $this->getMatch($url);
        if (!$matches) {
            return null;
        }

        $expectedMatches = count($this->getPathParameterNames());
        if (isset($matches[0][$expectedMatches + 1])) {
            return true;
        }

        return false;
    }


    /**
     * Returns all the path parameters found within the specified url.
     * Returns null if the url did not match according to the regular expression of THIS instance.
     * If the url matches the regular expression, but has no resource-id configured, the
     * resource id will not be available in the resulting array as a key.
     * @example
     *    // $this->urlTemplate = "/Container/{containerId}/Resources/{resourceId}";
     *    $output = $this->getPathParameters("https://localhost:8080/Container/1/Resources);
     *    // $output is now: ["containerId" => 1]
     *
     *    $output = $this->getPathParameters("https://localhost:8080/Container/1/Resources/3);
     *    // $output is now: ["containerId" => 1, "resourceId" => 3]
     *
     *
     * @param string $url
     *
     * @return array<string, string>|null
     */
    public function getPathParameters(string $url): ?array
    {
        $matches = $this->getMatch($url);
        if (!$matches) {
            return null;
        }

        $res = [];
        $parameterNames = $this->getPathParameterNames();
        foreach ($parameterNames as $name) {
            if (array_key_exists($name, $matches[0])) {
                $res[$name] = $matches[0][$name];
            }
        }

        return $res;
    }


    /**
     * Returns $resourceName, if the url matched the pattern available with this instance.
     * Returns null otherwise.
     *
     * @param string $url
     * @return ?string
     */
    public function getResourceName(string $url): ?string
    {
        if (!$this->getMatch($url)) {
            return null;
        }

        return $this->resourceName;
    }


    /**
     * Returns all parameter names that where found in the url-template.
     *
     * @return array<int, string>
     */
    public function getPathParameterNames(): array
    {
        $this->prepareRegex();
        return $this->pathParameterNames;
    }


    /**
     * @return string
     */
    public function getUrlTemplate(): string
    {
        return $this->urlTemplate;
    }


    /**
     * Tries to match the $url given the pattern available with this class.
     *
     * @param string $url
     * @return array<int, array<int, string>>|null
     */
    public function getMatch(string $url): ?array
    {
        $regex = $this->getRegexString();

        preg_match_all($regex, $url, $matches, PREG_SET_ORDER, 0);

        if (count($matches) === 0) {
            return null;
        }

        return $matches;
    }


    /**
     * @inheritdoc
     * @return array<int, string>
     */
    public function toArray(): array
    {
        return [
            $this->getUrlTemplate(), $this->resourceName
        ];
    }
}
