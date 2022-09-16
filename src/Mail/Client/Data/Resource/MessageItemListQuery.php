<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Mail\Client\Data\Resource;

use Conjoon\Core\Data\Resource\ResourceQuery;
use Conjoon\Core\Data\SortInfoList;

/**
 * ResourceQuery implementation for querying MessageItemList.
 *
 */
abstract class MessageItemListQuery extends ResourceQuery
{
    /**
     * Returns the int-value of "page[start]".
     *
     * @return int
     */
    abstract public function getStart(): int;


    /**
     * Returns the limit specified for this query.
     * Returns "null" if no limit was specified.
     *
     * @return int|null
     */
    abstract public function getLimit(): ?int;


    /**
     * Returns the fields that should be queried. If no fields where specified, this implementation
     * will return the default fields of the resource target for this query.
     *
     * @return array
     */
    abstract public function getFields(): array;


    /**
     * Returns sort information for this query.
     *
     * @return SortInfoList
     */
    abstract public function getSort(): SortInfoList;


    /**
     * @inheritdoc
     */
    abstract public function getResourceTarget(): MessageItem;
}