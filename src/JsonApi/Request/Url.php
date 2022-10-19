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

use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Http\Url as HttpUrl;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;

/**
 * Url specific for JSON:API requests.
 * Provides information about the targeted resource in form of an ObjectDescription.
 *
 * @example
 *
 *    // single resource
 *    $url = new JsonApiUrl("MessageItems/1", new Resource/MessageItem());
 *
 *    // collection
 *    $url = new JsonApiUrl("MessageItems", new Resource/MessageItem(), true);
 *
 */
class Url extends HttpUrl
{
    /**
     * @var ObjectDescription
     */
    protected readonly ObjectDescription $resourceTarget;

    /**
     * @var bool
     */
    protected readonly bool $targetsResourceCollection;

    /**
     * Constructor.
     *
     * @param string $url
     * @param ObjectDescription $resourceTarget
     * @param bool $targetsResourceCollection
     */
    public function __construct(
        string $url,
        ObjectDescription $resourceTarget,
        bool $targetsResourceCollection = false
    ) {
        parent::__construct($url);
        $this->resourceTarget = $resourceTarget;
        $this->targetsResourceCollection = $targetsResourceCollection;
    }


    /**
     * @inheritdoc
     */
    public function getQuery(): ?JsonApiQuery
    {
        if ($this->queryBuild) {
            return $this->query;
        }

        $this->queryBuild = true;
        $query = parse_url($this->url, PHP_URL_QUERY);
        if ($query) {
            $this->query = new JsonApiQuery($query, $this->getResourceTarget());
        }

        return $this->query;
    }


    /**
     * @return ObjectDescription
     */
    public function getResourceTarget(): ObjectDescription
    {
        return $this->resourceTarget;
    }


    /**
     * @return bool
     */
    public function targetsResourceCollection(): bool
    {
        return $this->targetsResourceCollection;
    }
}
