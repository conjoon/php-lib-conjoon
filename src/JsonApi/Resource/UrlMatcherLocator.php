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

/**
 * Provides a contract for locating resource object descriptions targeted by Requests.
 * Class names are computed w/o prefix or postfix. Pluralized names will be transformed to their singular
 * representations.
 * An instance of this class expects an array of regular expressions that should be used for matching an url of
 * a request to its corresponding resource object description.
 *
 * @example
 *
 *   $matchers = [
 *        "/MailAccounts\/.+\/MailFolders\/.*\/(MessageItems)\/*$/m"
 *   ]
 *
 *   $locator = new UrlMatcherLocator("App\\Resource", $matchers);
 *
 *   // ...
 *   // $request available with url http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageItems
 *   $locator->getResourceTarget($request); // App\Resource\MessageItem;
 *
 *   // ...
 *   // $request available with url http://localhost/MailAccounts/dev/MailFolders/INBOX/MessageItems/123
 *   $locator->getResourceTarget($request); // App\Resource\MessageItem;
 *
 */
class UrlMatcherLocator implements Locator
{
    /**
     * @var string
     */
    protected string $resourceBucket;

    /**
     * @var array
     */
    protected array $matchers;


    /**
     * Creates a new instance of the UrlMatcherLocator.
     *
     * @param string $resourceBucket The namespace where the computed class names
     * can be found.
     * @param array $matchers an array of regular expressions that should be used with
     * the url of the request to determine the requested resource target
     */
    public function __construct(string $resourceBucket, array $matchers)
    {
        $this->resourceBucket = $resourceBucket;
        $this->matchers = $matchers;
    }


    /**
     * Return the resource object description for the resource targeted by the specified
     * $request by evaluating (RESTful) urls and transforming path parts into class names.
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

        $className = $this->computeClassName($url);

        if ($className === null) {
            return null;
        }

        $resourceBucket = $this->getResourceBucket();

        $fqn = "$resourceBucket\\$className";

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
     * Computes the class name based on the given url.
     *
     * @param string $url
     *
     * @return string|null The computed class name, or null if the class name could not be computed
     */
    protected function computeClassName(string $url): ?string
    {
        $matchers = $this->getMatchers();
        $match    = null;
        foreach ($matchers as $regex) {
            preg_match_all($regex, $url, $matches, PREG_SET_ORDER, 0);

            if ($matches) {
                $match = $matches[0][1];
                break;
            }
        }

        if (!$match) {
            return null;
        }

        if (str_ends_with($match, "s")) {
            return substr($match, 0, -1);
        }

        return $match;
    }


    /**
     * @return string
     */
    protected function getResourceBucket(): string
    {
        return $this->resourceBucket;
    }


    /**
     * @return array
     */
    protected function getMatchers(): array
    {
        return $this->matchers;
    }
}
