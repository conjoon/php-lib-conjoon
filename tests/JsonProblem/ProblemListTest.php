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

namespace Tests\Conjoon\JsonProblem;

use Conjoon\JsonProblem\AbstractProblem;
use Conjoon\Core\AbstractList;
use Conjoon\JsonProblem\ProblemList;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Tests ProblemList.
 */
class ProblemListTest extends TestCase
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
        $this->assertSame(AbstractProblem::class, $list->getEntityType());
    }


    /**
     * Tests toArray() and toJson()
     */
    public function testToArrayAndJson()
    {
        $list = $this->createList();

        $entry1 = $this->createMockForAbstract(
            AbstractProblem::class,
            ["toArray"]
        );
        $entry1->expects($this->once())->method("toArray")->willReturn(["entry1"]);
        $entry2 = $this->createMockForAbstract(
            AbstractProblem::class,
            ["toArray"]
        );
        $entry2->expects($this->once())->method("toArray")->willReturn(["entry2"]);

        $list[] = $entry1;
        $list[] = $entry2;

        $this->assertEquals([
            ["entry1"], ["entry2"]
        ], $list->toArray());

        $this->runToJsonTest(new ProblemList());
    }


    /**
     * @returnOperandList
     */
    protected function createList(): ProblemList
    {
        return new ProblemList();
    }
}
