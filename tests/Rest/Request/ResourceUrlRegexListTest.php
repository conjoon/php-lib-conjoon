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

namespace Tests\Conjoon\Rest\Request;

use Conjoon\Core\AbstractList;
use Conjoon\Rest\Request\ResourceUrlRegex;
use Conjoon\Rest\Request\ResourceUrlRegexList;
use Conjoon\Http\Url;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Tests\TestCase;

/**
 * tests ResourceUrlRegexList
 */
class ResourceUrlRegexListTest extends TestCase
{
    /**
     * Tests constructor
     * @return void
     */
    public function testClass(): void
    {
        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(ResourceUrlRegex::class, $list->getEntityType());
    }


    /**
     * Tests getMatch()
     * @return void
     * @throws ReflectionException
     */
    public function testGetMatch(): void
    {
        $list = $this->createList();
        $url  = new Url("url");
        $urlStr = $url->toString();

        $matches = $this->makeAccessible($list, "matches", true);

        $getRegexMock = function ($returnValue, $numCalls) use ($urlStr) {
            $regex = $this->getMockBuilder(ResourceUrlRegex::class)
                ->disableOriginalConstructor()
                ->onlyMethods(["getMatch"])
                ->getMock();
            $regex->expects($this->exactly($numCalls))
                ->method("getMatch")
                ->with($urlStr)->willReturn($returnValue);

            return $regex;
        };
        $regex1 = $getRegexMock(null, 2);
        $regex2 = $getRegexMock([], 2);
        $regex3 = $getRegexMock(null, 0);
        $regex4 = $getRegexMock(null, 0);

        $list[] = $regex1;
        $list[] = $regex2;
        $list[] = $regex3;

        $this->assertSame($regex2, $list->getMatch($url));
        // force re-call to make sure cache is used
        $this->assertSame($regex2, $list->getMatch($url));

        /** @phpstan-ignore-next-line */
        $this->assertNotEmpty($matches->getValue($list));

        $list[] = $regex4;
        /** @phpstan-ignore-next-line */
        $this->assertEmpty($matches->getValue($list));

        $this->assertSame(
            $regex2,
            $list->getMatch($url)
        );

        unset($list[4]);
        /** @phpstan-ignore-next-line */
        $this->assertEmpty($matches->getValue($list));
    }


    /**
     * Tests toArray()
     * @return void
     */
    public function testToArray(): void
    {
        $list = new ResourceUrlRegexList();
        $this->assertEquals([], $list->toArray());

        /** @var ResourceUrlRegex&MockObject $resourceUrlRegex */
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
