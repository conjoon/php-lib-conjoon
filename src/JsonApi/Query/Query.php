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

use Conjoon\Core\Data\StringStrategy;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Core\Data\Resource\ObjectDescription;

/**
 * Query validated for JSON:API specifications, describing access to a $resourceTarget
 * described by  ObjectDescription.
 *
 */
class Query extends HttpQuery
{
    /**
     * @var HttpQuery $query
     */
    protected HttpQuery $query;

    /**
     * @var ObjectDescription
     */
    protected ObjectDescription $resourceTarget;

    /**
     * Constructor.
     *
     * @param HttpQuery $query The original query decorated by this class.
     * @param ObjectDescription $resourceTarget The resource object description this query is interested in
     */
    public function __construct(HttpQuery $query, ObjectDescription $resourceTarget)
    {
        $this->query          = $query;
        $this->resourceTarget = $resourceTarget;
    }


    /**
     * @return ObjectDescription
     */
    public function getResourceTarget(): ObjectDescription
    {
        return $this->resourceTarget;
    }


    /**
     * @inheritdoc
     */
    public function getParameter(string $name): ?Parameter
    {
        return $this->query->getParameter($name);
    }


    /**
     * @inheritdoc
     */
    public function getAllParameters(): ParameterList
    {
        return $this->query->getAllParameters();
    }


    /**
     * @inheritdoc
     */
    public function getAllParameterNames(): array
    {
        return $this->query->getAllParameterNames();
    }


    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->query->getName();
    }


    /**
     * @inheritdoc
     */
    public function getSource(): object
    {
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        if ($stringStrategy) {
            return $stringStrategy->toString($this);
        }

        return $this->query->toString();
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            "query" => $this->toString()
        ];
    }
}
