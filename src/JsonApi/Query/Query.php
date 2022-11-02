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

namespace Conjoon\JsonApi\Query;

use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Net\Uri\Component\Query as HttpQuery;

/**
 * Query validated for JSON:API specifications, providing access to a $resourceTarget
 * described by ObjectDescription.
 *
 */
class Query extends HttpQuery
{
    /**
     * @var ObjectDescription
     */
    protected ObjectDescription $resourceTarget;

    /**
     * Constructor.
     *
     * @param string $queryString
     * @param ObjectDescription $resourceTarget The resource object description this query is interested in
     */
    public function __construct(string $queryString, ObjectDescription $resourceTarget)
    {
        parent::__construct($queryString);
        $this->resourceTarget = $resourceTarget;
    }


    /**
     * @return ObjectDescription
     */
    public function getResourceTarget(): ObjectDescription
    {
        return $this->resourceTarget;
    }
}
