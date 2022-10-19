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

namespace Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\Sort\SortInfoList;
use Conjoon\Data\Filter\Filter;

/**
 * ResourceQuery implementation for querying MessageItemList.
 *
 */
abstract class MessageItemListQuery extends MessageItemQuery
{
    /**
     * Returns the offset of the first message item requested with the query.
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
     * Returns sort information for this query.
     * Returns null if no sort information is available.
     *
     * @return SortInfoList|null
     */
    abstract public function getSort(): ?SortInfoList;


    /**
     * Returns filter information for this query.
     * Returns null if no filter information is  available.
     *
     * @return Filter|null
     */
    abstract public function getFilter(): ?Filter;
}
