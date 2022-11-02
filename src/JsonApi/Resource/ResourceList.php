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

use Conjoon\Core\AbstractList;
use InvalidArgumentException;
use OutOfBoundsException;
use TypeError;

/**
 * An abstract list maintaining entities of the type Resource.
 * This implementation makes sure that no URI duplicates may exist in a ResourceList-object.
 *
 * @extends AbstractList<Resource>
 */
class ResourceList extends AbstractList
{
    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException|OutOfBoundsException|TypeError
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertTypeFor($value);

        /**
         * @var Resource $value
         */
        $value = $this->uriExists($value);

        $this->doInsert($offset, $value);
    }

    /**
     * Returns the Resource itself if its URI does not already exist in *this* ResourceList.
     *
     * @param Resource $resource
     * @return Resource
     *
     * @throws InvalidArgumentException if the URI of the resource was already registered with
     * this ResourceList.
     */
    private function uriExists(Resource $resource): Resource
    {
        $uri = $resource->getUri();

        if (count(array_filter($this->data, fn ($res) => $res->getUri()->equals($uri))) > 0) {
            throw new InvalidArgumentException(
                "A resource for the URI \"" . $uri->toString() . "\" already exists"
            );
        }

        return $resource;
    }


    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return Resource::class;
    }
}
