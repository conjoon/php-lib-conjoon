<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Core;

use ArrayAccess;
use Conjoon\Core\AbstractList;
use Countable;
use Iterator;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\TestCase;
use TypeError;

/**
 * Class AbstractListTest
 * @package Tests\Conjoon\Util
 */
class AbstractListTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     * @return void
     */
    public function testConstructor(): void
    {

        $abstractList = $this->getMockForAbstractList();
        $this->assertSame(stdClass::class, $abstractList->getEntityType());
        $this->assertInstanceOf(Countable::class, $abstractList);
        $this->assertInstanceOf(ArrayAccess::class, $abstractList);
        $this->assertInstanceOf(Iterator::class, $abstractList);
    }

    /**
     * Tests OutOfBoundsException /w string as key
     * @return void
     */
    public function testOffsetSetWithStringAndOutOfBoundsException(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $abstractList = $this->getMockForAbstractList();
        $abstractList["1"] = "foo";
    }


    /**
     * Tests ArrayAccess /w type exception
     * @return void
     */
    public function testArrayAccessException(): void
    {
        $this->expectException(TypeError::class);

        $abstractList = $this->getMockForAbstractList();
        $abstractList[] = "foo";
    }


    /**
     * Tests ArrayAccess
     * @return void
     */
    public function testArrayAccessAndCountable(): void
    {
        $abstractList = $this->getMockForAbstractList();

        $cmpList = [
            new stdClass(),
            new stdClass()
        ];

        $abstractList[] = $cmpList[0];
        $abstractList[] = $cmpList[1];

        $this->assertSame(2, count($abstractList));

        foreach ($abstractList as $key => $item) {
            $this->assertSame($cmpList[$key], $item);
        }
    }


    /**
     * Tests Arrayable
     * @return void
     */
    public function testToArray(): void
    {
        $abstractList = $this->getMockForAbstractList();

        $cmpList = [
            new stdClass(),
            new stdClass()
        ];

        $abstractList[] = $cmpList[0];
        $abstractList[] = $cmpList[1];

        $this->assertEquals([
            $abstractList[0],
            $abstractList[1]
        ], $abstractList->toArray());
    }


    /**
     * Tests map()
     * @return void
     */
    public function testMap(): void
    {
        $abstractList = $this->getMockForAbstractList();

        $cmpList = [
            new stdClass(),
            new stdClass()
        ];

        $cmpList[0]->foo = 1;
        $cmpList[0]->bar = 2;
        $cmpList[1]->foo = 3;
        $cmpList[1]->bar = 4;

        $abstractList[] = $cmpList[0];
        $abstractList[] = $cmpList[1];

        $mock = $this->getMockBuilder(stdClass::class)
                     ->addMethods(["mapCallback"])->getMock();

        $mock->expects($this->exactly(2))
            ->method("mapCallback")->withConsecutive([$cmpList[0]], [$cmpList[1]])
            ->willReturnOnConsecutiveCalls($cmpList[0]->foo * 2, $cmpList[1]->foo * 2);


        /** @phpstan-ignore-next-line */
        $cb = $mock->mapCallback(...);
        $this->assertEquals(
            [2, 6],
            $abstractList->map($cb)
        );
    }


    /**
     * Tests findBy()
     * @return void
     */
    public function testFindBy(): void
    {
        $abstractList = $this->getMockForAbstractList();

        $cmpList = [
            new stdClass(),
            new stdClass()
        ];

        $cmpList[0]->foo = 1;
        $cmpList[0]->bar = 2;
        $cmpList[1]->foo = 3;
        $cmpList[1]->bar = 4;

        $abstractList[] = $cmpList[0];
        $abstractList[] = $cmpList[1];

        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(["findCallback"])->getMock();

        $mock->expects($this->exactly(2))
            ->method("findCallback")->withConsecutive([$cmpList[0]], [$cmpList[1]])
            ->willReturnOnConsecutiveCalls(false, true);

        /** @phpstan-ignore-next-line */
        $cb = $mock->findCallback(...);
        $this->assertSame(
            $cmpList[1],
            $abstractList->findBy($cb)
        );
    }

    /**
     * Tests peek()
     * @return void
     */
    public function testPeek(): void
    {
        $abstractList = $this->getMockForAbstractList();

        $this->assertNull($abstractList->peek());

        $one = new stdClass();
        $two = new stdClass();

        $abstractList[] = $one;
        $abstractList[] = $two;

        $this->assertSame($two, $abstractList->peek());
    }


    /**
     * Tests make()
     * @return void
     */
    public function testMake(): void
    {
        $abstractList = new class extends AbstractList {
            public function getEntityType(): string
            {
                return stdClass::class;
            }
        };

        $one = new stdClass();
        $two = new stdClass();

        $list = $abstractList::make($one, $two);

        $this->assertInstanceOf($abstractList::class, $list);

        $this->assertSame($list[0], $one);
        $this->assertSame($list[1], $two);
    }


// ---------------------
//    Helper Functions
// ---------------------

    /**
     * @return MockObject&AbstractList<stdClass>
     */
    protected function getMockForAbstractList(): AbstractList&MockObject
    {

        $mock = $this->getMockForAbstractClass(AbstractList::class);
        $mock->expects($this->any())
             ->method("getEntityType")
             ->will($this->returnValue(stdClass::class));

        return $mock;
    }
}
