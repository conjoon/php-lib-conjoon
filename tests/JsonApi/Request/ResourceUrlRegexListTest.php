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

namespace Tests\Conjoon\JsonApi\Request;

use Conjoon\Core\Data\AbstractList;
use Conjoon\JsonApi\Request\ResourceUrlRegex;
use Conjoon\JsonApi\Request\ResourceUrlRegexList;
use Tests\TestCase;

/**
 * tests ResourceUrlRegexList
 */
class ResourceUrlRegexListTest extends TestCase
{
    /**
     * Tests constructor
     */
    public function testClass()
    {
        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(ResourceUrlRegex::class, $list->getEntityType());
    }


    /**
     * Tests toArray
     * @return void
     */
    public function testToArray()
    {
        $list = new ResourceUrlRegexList();
        $this->assertEquals([], $list->toArray());

        $resourceUrlRegex = $this->createMockForAbstract(
            ResourceUrlRegex::class,
            ["toArray"],
            ["", 1, 2]
        );
        $resourceUrlRegex->expects($this->once())->method("toArray")->willReturn([]);

        $list = new ResourceUrlRegexList();
        $list[] = $resourceUrlRegex;

        $this->assertEquals([[]], $list->toArray());
    }


    /**
     * @return ResourceUrlRegexList
     */
    protected function createList(): ResourceUrlRegexList
    {
        return new ResourceUrlRegexList();
    }
}
