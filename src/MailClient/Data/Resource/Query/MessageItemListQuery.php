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

use Conjoon\Data\Resource\RepositoryQuery;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Data\Sort\SortInfoList;
use Conjoon\MailClient\Data\Resource\DefaultMessageBodyOptions;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\Data\Resource\Options;

/**
 * RepositoryQuery implementation for querying MessageItemList.
 *
 */
abstract class MessageItemListQuery extends RepositoryQuery
{
    /**
     * Returns the offset of the first message item requested with the query.
     *
     * @return int
     */
    abstract public function getStart(): int;

    /**
     * Returns a ResourceDescriptionList of all resources that should be considered
     * with this query.
     *
     * @return ResourceDescriptionList|null
     */
    abstract public function getInclude(): ResourceDescriptionList;

    /**
     * Returns the limit specified for this query.
     *
     * @return int
     */
    abstract public function getLimit(): int;


    /**
     * Returns sort information for this query.
     * Returns null if no sort information is available.
     *
     * @return SortInfoList|null
     */
    abstract public function getSort(): ?SortInfoList;


    /**
     * Returns the fields that should be queried for the specified ResourceDescription's
     * className.
     *
     * @param string|null $className
     *
     * @return ?array Returns the array of fields that should be queried, or null if the
     * specified ResourceDescription was not found in this class ResourceDescriptionList
     */
    abstract public function getFields(string $className = null): ?array;


    abstract public function getOptions(string $className = null): ?DefaultMessageBodyOptions;

    /**
     * This RepositoryQuery targets MessageItem.
     */
    public function getResourceDescription(): MessageItemDescription
    {
        return MessageItemDescription::getInstance();
    }
}
