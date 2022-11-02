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

namespace Tests\Conjoon\Data\Sort\Sort;

use Conjoon\Core\Contract\Jsonable;
use Conjoon\Data\Sort\SortDirection;
use Conjoon\Data\Sort\SortInfo;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Tests SortOrder.
 */
class SortInfoTest extends TestCase
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
        $sort = new SortInfo("subject", SortDirection::ASC);
        $this->assertInstanceOf(Jsonable::class, $sort);
        $this->assertSame("subject", $sort->getField());
        $this->assertSame(SortDirection::ASC, $sort->getDirection());


        $this->assertSame([
            "field" => "subject",
            "direction" => SortDirection::ASC->value
        ], $sort->toArray());
    }


    /**
     * Tests toJson()
     */
    public function testToJson()
    {
        $this->runToJsonTest(
            $this->createMockForAbstract(SortInfo::class, [], ["subject", SortDirection::ASC])
        );
    }
}
