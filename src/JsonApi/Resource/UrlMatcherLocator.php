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

namespace Conjoon\JsonApi\Resource;

use Conjoon\Core\Exception\ClassNotFoundException;
use Conjoon\Core\Exception\InvalidTypeException;
use Conjoon\Http\Request\Request;
use Conjoon\JsonApi\Request\ResourceUrlParser;

/**
 * Provides a contract for locating resource object descriptions targeted by Requests.
 *
 * @example
 *
 *   $matchers = new ResourceUrlRegexList();
 *   $matchers[] = new ResourceUrlRegex("/MailAccounts\/.+\/MailFolders\/.*\/(MessageItems)(\/)*$/m", 1, 2);
 *
 *   $parser = new ResourceUrlParser($matchers, "App\\Resource\\{0}")
 *   $locator = new UrlMatcherLocator($parser);
 *
 *   // ...
 *   // $request available with url http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageItems
 *   $locator->getResourceTarget($request); // App\Resource\MessageItem;
 *
 */
class UrlMatcherLocator implements Locator
{
    /**
     * @var ResourceUrlParser
     */
    protected ResourceUrlParser $resourceUrlParser;


    /**
     * Creates a new instance of the UrlMatcherLocator.
     *
     * @param ResourceUrlParser $resourceUrlParser
     */
    public function __construct(ResourceUrlParser $resourceUrlParser)
    {
        $this->resourceUrlParser = $resourceUrlParser;
    }


    /**
     * Return the resource object description for the resource targeted by the specified
     * $request if this class' $resourceUrlParser parses the request's url successfully.
     *
     * @param Request $request The $request of which the value of getUrl() will be used for
     * determining the class name of the ObjectDescription
     *
     * @return ObjectDescription|null The resource's ObjectDescription, or null if no instance
     * could be determined.
     *
     * @throws ClassNotFoundException|InvalidTypeException
     *
     * @see computeClassName
     */
    public function getResourceTarget(Request $request): ?ObjectDescription
    {
        $url = $request->getUrl();

        $fqn = $this->resourceUrlParser->parse($url);

        if ($fqn === null) {
            return null;
        }

        if (!class_exists($fqn)) {
            throw new ClassNotFoundException(
                "Cannot find class with name $fqn"
            );
        }

        if (!is_a($fqn, ObjectDescription::class, true)) {
            throw new InvalidTypeException(
                "$fqn must be an instance of " . ObjectDescription::class . ", was " . get_parent_class($fqn)
            );
        }

        return new $fqn();
    }


    /**
     * @return ResourceUrlParser
     */
    public function getResourceUrlParser(): ResourceUrlParser
    {
        return $this->resourceUrlParser;
    }
}
