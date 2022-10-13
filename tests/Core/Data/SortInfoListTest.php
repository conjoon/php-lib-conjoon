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

namespace Tests\Conjoon\Core\Data;

use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Data\SortDirection;
use Conjoon\Core\Data\SortInfo;
use Conjoon\Core\Data\SortInfoList;
use Conjoon\Core\Data\AbstractList;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Tests SortInfoList.
 */
class SortInfoListTest extends TestCase
{
    use JsonableTestTrait;

// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass()
    {

        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);
        $this->assertInstanceOf(Jsonable::class, $list);

        $this->assertSame(SortInfo::class, $list->getEntityType());
    }


    /**
     * Tests toArray()
     */
    public function testToArray()
    {
        $list = $this->createList();

        $entry1 = $this->createMockForAbstract(
            SortInfo::class,
            ["toArray"],
            ["subject", SortDirection::ASC]
        );
        $entry1->expects($this->once())->method("toArray")->willReturn([]);
        $entry2 = $this->createMockForAbstract(
            SortInfo::class,
            ["toArray"],
            ["subject", SortDirection::ASC]
        );
        $entry2->expects($this->once())->method("toArray")->willReturn([]);

        $list[] = $entry1;
        $list[] = $entry2;

        $this->assertEquals([
            [], []
        ], $list->toArray());
    }


    /**
     * Tests toJson()
     */
    public function testToJson()
    {
        $this->runToJsonTest($this->createMockForAbstract(SortInfoList::class));
    }


    /**
     * @return SortInfoList
     */
    protected function createList(): SortInfoList
    {
        return new SortInfoList();
    }
}
