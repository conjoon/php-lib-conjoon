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

use Conjoon\Http\Url;

/**
 * Class providing functionality for resource urls that require requested resource(s) to be mapped
 * to arbitrary strings.
 * Resources in urls are identified by ResourceUrlRegex's.
 *
 * @example
 *
 *   $urlRegexList = new ResourceUrlRegexList();
 *   $urlRegexList[] = new ResourceUrlRegex("/(MailAccounts)(\/)?[^\/]*$/m", 1, 2);
 *   $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m", 1, 2);
 *   $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/(MailFolders)(\/)?[^\/]*$/m", 1, 2);
 *
 *   $parser = new Conjoon\JsonApi\Request\ResourceUrlParser(
 *       $urlRegexList,
 *       "JsonApi\\Resource\\{0}",
 *       "JsonApi\\Resource\\{0}Collection",
 *   );
 *
 *   $parser->parse("MailAccounts/MailFolders/INBOX"); // null
 *   $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems"); // "JsonApi\\Resource\\MessageItemCollection"
 *   $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems/123"); // "JsonApi\\Resource\\MessageItem"
 */
class ResourceUrlParser
{
    /**
     * @var string
     */
    protected string $template;

    /**
     * @var ?string
     */
    protected ?string $collectionTemplate = null;


    /**
     * @var ResourceUrlRegexList
     */
    protected ResourceUrlRegexList $resourceUrlRegexList;


    /**
     * Creates a new instance of ResourceUrlToClassName.
     *
     * @param ResourceUrlRegexList $resourceUrlRegexList a list of ResourceUrlRegex's that should be used with
     * the url of the request to determine the requested resource target
     * @param string $template The template for the computed classname, whereas {0} is the placeholder
     * for the determined resource name
     * @param string|null $collectionTemplate The template used for a collection. Optional. If not specified,
     * collection requests will fall back to $template.
     *
     */
    public function __construct(ResourceUrlRegexList $resourceUrlRegexList, string $template, ?string $collectionTemplate = null)
    {
        $this->resourceUrlRegexList = $resourceUrlRegexList;

        $this->template = $template;
        $this->collectionTemplate = $collectionTemplate;
    }


    /**
     * Tries to match the given $url with any regular expressions defined with $resourceUrlRegexList
     * and transforms to a string according to the specified templates, if possible.
     *
     * @param Url $url
     *
     * @return string|null The computed string, or null if no regular expressions from the given $resourceUrlRegexList
     * matched.
     */
    public function parse(Url $url): ?string
    {
        $matchers = $this->getResourceUrlRegexList();
        $match    = null;
        $isCollection = false;
        foreach ($matchers as $resourceUrlRegex) {
            $match = $resourceUrlRegex->getResourceName($url);
            if ($match) {
                $isCollection = !$resourceUrlRegex->isSingleRequest($url);
                break;
            }
        }

        if (!$match) {
            return null;
        }

        $collectionTemplate = $this->getCollectionTemplate();

        return str_replace(
            "{0}",
            $match,
            $collectionTemplate && $isCollection ? $collectionTemplate : $this->getTemplate()
        );
    }


    /**
     * Returns false if the submitted $url could be parsed given the available
     * $resourceUrlRegexList and represents a request to a single resource, true
     * if it represents a request to a resource collection.
     * Returns null if no configured regex matched the url.
     *
     * @param Url $url
     *
     * @return bool|null
     */
    public function targetsResourceCollection(Url $url): bool|null
    {
        $matchers = $this->getResourceUrlRegexList();

        foreach ($matchers as $resourceUrlRegex) {
            if ($resourceUrlRegex->getResourceName($url) !== null) {
                return !$resourceUrlRegex->isSingleRequest($url);
            }
        }

        return null;
    }


    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }


    /**
     * @return string|null
     */
    public function getCollectionTemplate(): ?string
    {
        return $this->collectionTemplate;
    }


    /**
     * @return ResourceUrlRegexList
     */
    public function getResourceUrlRegexList(): ResourceUrlRegexList
    {
        return $this->resourceUrlRegexList;
    }
}
