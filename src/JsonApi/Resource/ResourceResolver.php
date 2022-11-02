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

use Conjoon\Net\Uri;

/**
 * Provides functionality for resolving URIs to an actual resource.
 *
 * @example
 *
 *    class EmployeeResource implements Resource {
 *        public function getUri(): Template
 *        {
 *            return new Template("/directory/employees/{id}");
 *        }
 *    }
 *
 *    class EmployeeResourceCollection extends EmployeeResource implements ResourceCollection
 *    {
 *        public function getUri(): Template
 *        {
 *            return new Template("/directory/employees");
 *        }
 *    }
 *
 *    $resources = [
 *       new EmployeeResource(),
 *       new EmployeeResourceCollection()
 *   ];
 *
 *   $resolver = new ResourceResolver(ResourceList::make(...$resources));
 *   $resolver->resolve(Uri::make("https://localhost:8080/directory/employees/1")); // EmployeeResource
 *   $resolver->resolve(Uri::make("https://localhost:8080/directory/employees")); // EmployeeResourceCollection
 *   $resolver->resolve(Uri::make("https://localhost:8080/directory/shipments")); // null
 *
 */
class ResourceResolver
{
    /**
     * @var ResourceList
     */
    private ResourceList $locations;


    /**
     * @param ResourceList $locations
     */
    public function __construct(ResourceList $locations)
    {
        $this->locations = $locations;
    }


    /**
     * Resolves the specified URI and returns the Resource that is located there.
     * Returns null if the URI did not target a registered resource with this ResourceResolver.
     *
     * @param Uri $uri
     * @return Resource|null
     */
    public function resolve(Uri $uri): ?Resource
    {
        foreach ($this->locations as $location) {
            if ($location->getUri()->match($uri) !== null) {
                return $location;
            }
        }
        return null;
    }
}
