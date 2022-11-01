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

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\Core\AbstractList;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceList;
use Conjoon\Net\Uri\Component\Path\Template;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * tests ResourceList
 */
class ResourceListTest extends TestCase
{
    /**
     * Tests constructor
     */
    public function testClass(): void
    {
        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(Resource::class, $list->getEntityType());
    }


    /**
     * tests offsetSet(), makes sure no URI duplicates may occur
     * @return void
     */
    public function testOffsetSet(): void
    {
        $resource = function (string $uri): MockObject&Resource {
            /**
             * @var MockObject&Resource $res
             */
            $res = $this->createMockForAbstract(Resource::class, ["getUri"]);
            $res->expects($this->any())->method("getUri")->willReturn(new Template($uri));
            return $res;
        };

        $resourceList = new ResourceList();
        $resourceList[] = $resource("/uri1");
        $resourceList[] = $resource("/uri2");
        $resourceList[] = $resource("/uri3");

        try {
            $resourceList[] = $resource("/uri2");
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString(
                strtolower("a resource for the URI \"/uri2\" already exists"),
                strtolower($e->getMessage())
            );
        }
    }


    /**
     * @return ResourceList
     */
    protected function createList(): ResourceList
    {
        $list = new ResourceList();


        return $list;
    }
}
