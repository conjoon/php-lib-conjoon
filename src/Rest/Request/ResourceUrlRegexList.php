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

use Conjoon\Core\AbstractList;
use Conjoon\Http\Url;
use Iterator;
use ArrayAccess;

/**
 * A list for managing ResourceUrlRegex-instances.
 * Provides functionality for matching Urls to ResourceUrlRegex for reducing overhead
 * while trying to find matches. Changes to the list itself will reset the cache.
 *
 * @extends AbstractList<ResourceUrlRegex>
 */
class ResourceUrlRegexList extends AbstractList
{
    /**
     * @var array<string, ?ResourceUrlRegex>
     */
    private array $matches = [];

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return ResourceUrlRegex::class;
    }


    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->matches = [];
        parent::offsetSet($offset, $value);
    }


    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        $this->matches = [];
        parent::offsetUnset($offset);
    }


    /**
     * Probes all available entries in this list for a match with the specified
     * url, and returns the ResourceUrlRegex that matched the Url.
     * If no match was found, null will be returned. Results are cached so that for a
     * specific url the list is inspected only once.
     *
     * @param Url $url
     * @return ResourceUrlRegex|null
     *
     * @see offsetSet
     * @see offsetUnset
     */
    public function getMatch(Url $url): ?ResourceUrlRegex
    {
        $urlStr = $url->toString();

        if (array_key_exists($urlStr, $this->matches)) {
            return $this->matches[$urlStr];
        }

        $this->matches[$urlStr] = null;
        foreach ($this->data as $resourceUrlRegex) {
            $match = $resourceUrlRegex->getMatch($urlStr);
            if ($match !== null) {
                $this->matches[$urlStr] = $resourceUrlRegex;
                break;
            }
        }

        return $this->matches[$urlStr];
    }


    /**
     * @inheritdoc
     * @return array <int, array<int, string>>
     */
    public function toArray(): array
    {
        $res = [];

        foreach ($this->data as $entry) {
            /** @var ResourceUrlRegex $entry */
            $res[] = $entry->toArray();
        }

        return $res;
    }
}
