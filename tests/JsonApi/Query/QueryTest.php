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

namespace Tests\Conjoon\JsonApi\Query;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\Net\Uri\Component\Query;
use Tests\Conjoon\Net\Uri\Component\QueryTest as HttpQueryTest;

/**
 * Tests QueryParameter.
 *
 */
class QueryTest extends HttpQueryTest
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $query = $this->createTestInstance();
        $this->assertInstanceOf(Query::class, $query);
    }


    /**
     * Tests getResourceDescription()
     */
    public function testGetResourceDescription()
    {
        $resourceTarget = $this->createMockForAbstract(ResourceDescription::class);
        $query = new JsonApiQuery("", $resourceTarget);
        $this->assertSame($resourceTarget, $query->getResourceDescription());
    }


    /**
     * @param null $queryString
     * @return Query
     */
    protected function createTestInstance($queryString = null): JsonApiQuery
    {
        $resourceTarget = $this->createMockForAbstract(ResourceDescription::class);
        return new JsonApiQuery($queryString ?? "", $resourceTarget);
    }


    /**
     * @return string
     */
    protected function getTestedClass(): string
    {
        return JsonApiQuery::class;
    }
}
