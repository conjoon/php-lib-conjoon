<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Data\Resource;

use BadMethodCallException;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Data\ParameterBag;
use Conjoon\Data\Resource\RepositoryQuery;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Tests RepositoryQuery
 */
class ResourceQueryTest extends TestCase
{
    /**
     * Class functionality
     * @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testDelegates(): void
    {
        $bag = $this
                ->getMockBuilder(ParameterBag::class)
                ->setConstructorArgs([[
                    "foo" => "bar",
                    "bar" => "snafu"
                ]])
                ->getMock();

        $bag->expects($this->exactly(2))
            ->method("has")
            ->withConsecutive(["bar"], ["foo"])
            ->willReturnOnConsecutiveCalls(true, true);

        $bag->expects($this->exactly(1))
            ->method("toJson")
            ->willReturn([]);

        $bag->expects($this->exactly(3))
            ->method("__call")
            ->withConsecutive(
                ["getInt", ["bar"]],
                ["getString", ["foo"]],
                ["getBool", ["some"]]
            )->willReturnOnConsecutiveCalls(1, 2, null);

        $bag->expects($this->exactly(3))
            ->method("__get")
            ->withConsecutive(
                ["bar"],
                ["foo"],
                ["some" ]
            )->willReturnOnConsecutiveCalls(1, 2, null);

        /**
         * @var RepositoryQuery $resourceQuery
         */
        $resourceQuery = $this->getResourceQuery($bag);

        $this->assertInstanceOf(Jsonable::class, $resourceQuery);

        $this->assertTrue($resourceQuery->has("bar"));
        $this->assertTrue($resourceQuery->has("foo"));
        $this->assertSame([], $resourceQuery->toJson());

        $this->assertSame(1, $resourceQuery->getInt("bar"));
        $this->assertSame(2, $resourceQuery->getString("foo"));
        $this->assertNull($resourceQuery->getBool("some"));

        /** @see  https://github.com/phpstan/phpstan/discussions/4901 */
        /** @phpstan-ignore-next-line */
        $this->assertSame(1, $resourceQuery->bar);
        /** @phpstan-ignore-next-line */
        $this->assertSame(2, $resourceQuery->foo);
        /** @phpstan-ignore-next-line */
        $this->assertNull($resourceQuery->some);
    }


    /**
     * No method available
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testDelegateCallException(): void
    {
        $this->expectException(BadMethodCallException::class);
        /** @phpstan-ignore-next-line */
        $this->getResourceQuery(new ParameterBag())->getSomeThing("d");
    }


    /**
     * tests toJson()
     */
    public function testToJsonWithStrategy(): void
    {
        $json = [];
        $bag = $this
            ->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["toJson"])->getMock();

        /**
         * @var MockObject&JsonStrategy $strategy
         */
        $strategy = $this->createMockForAbstract(JsonStrategy::class);

        $query = $this->getResourceQuery($bag);

        $bag->expects($this->exactly(2))
            ->method("toJson")
            ->withConsecutive([$strategy], [null])->willReturnOnConsecutiveCalls($json, []);

        $this->assertSame($json, $query->toJson($strategy));
        $this->assertSame([], $query->toJson());
    }

    /**
     * @param ParameterBag $bag
     * @return RepositoryQuery&MockObject
     */
    protected function getResourceQuery(ParameterBag $bag): MockObject&RepositoryQuery
    {
        return $this->getMockForAbstractClass(
            RepositoryQuery::class,
            [$bag]
        );
    }
}
