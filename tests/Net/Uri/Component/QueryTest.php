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

namespace Tests\Conjoon\Net\Uri\Component;

use Conjoon\Net\Uri\Component\Query\ParameterTrait;
use Conjoon\Net\Uri\Component\Query;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests QueryParameter.
 *
 */
class QueryTest extends TestCase
{
    use StringableTestTrait;


    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $uses = class_uses($this->getTestedClass());
        /** @phpstan-ignore-next-line */
        $this->assertContains(ParameterTrait::class, $uses);
    }


    /**
     * tests getParameter()
     * @return void
     */
    public function testGetParameter(): void
    {
        $query = $this->createTestInstance("parameter=value");

        $parameter = $query->getParameter("parameter");
        $this->assertNotNull($parameter);

        // check idempotence
        $this->assertSame($parameter, $query->getParameter("parameter"));

        $missing = $query->getParameter("missing");
        $this->assertNull($missing);

        $this->assertSame($missing, $query->getParameter("missing"));

        $this->assertSame("value", $parameter->getValue());
    }

    /**
     * tests getParameter() with parameters compiled to arrays
     * @return void
     */
    public function testGetParameterWithBrackets(): void
    {
        $query = $this->createTestInstance(
            "fields[A]=valueA&fields[B]=valueB"
        );

        $this->assertNull($query->getParameter("fields"));

        $fieldsA = $query->getParameter("fields[A]");
        $this->assertNotNull($fieldsA);
        $this->assertSame($fieldsA, $query->getParameter("fields[A]"));
        $this->assertSame("valueA", $fieldsA->getValue());

        $fieldsB = $query->getParameter("fields[B]");
        $this->assertNotNull($fieldsB);

        $fieldsC = $query->getParameter("fields[C]");
        $this->assertNull($fieldsC);
    }


    /**
     * tests getAllParameterNames() with parameters compiled to arrays
     * @return void
     */
    public function testGetAllParameterNames(): void
    {
        $query = $this->createTestInstance();
        $this->assertEquals([], $query->getAllParameterNames());

        $query = $this->createTestInstance(
            "C=D&fields[A]=valueA&fields[B]=valueB"
        );

        $this->assertEquals(["C", "fields[A]", "fields[B]"], $query->getAllParameterNames());
    }


    /**
     * tests getAllParameters()
     * @return void
     */
    public function testGetAllParameters(): void
    {
        $query = $this->createTestInstance();
        $this->assertEquals([], $query->getAllParameters()->toArray());

        $query = $this->createTestInstance(
            "C=D&fields[A]=valueA&fields[B]=valueB"
        );

        $list = $query->getAllParameters();
        $this->assertSame($list, $query->getAllParameters());

        $this->assertEquals([
            "C" => "D",
            "fields[A]" => "valueA",
            "fields[B]" => "valueB"
        ], $query->getAllParameters()->toArray());
    }


    /**
     * tests getSource()
     * @return void
     */
    public function testGetSource(): void
    {
        $query = $this->createTestInstance();
        $this->assertSame($query, $query->getSource());
    }


    /**
     * tests toString() and getName() and toArray()
     * @return void
     */
    public function testToStringAndGetNameAndToArray(): void
    {

        $query = $this->createTestInstance("query=string");

        $this->assertSame("query=string", $query->__toString());
        $this->assertSame($query->__toString(), $query->getName());

        $this->assertSame([
            "query" => "string"
        ], $query->toArray());

        $this->runToStringTest(Query::class);
    }


    /**
     * tests getParameterBag()
     * @return void
     */
    public function testGetParameterBag(): void
    {

        $query = $this->createTestInstance("fields[MessageItem]=a,b,c&sort=subject,-date");

        $parameterBag = $query->getParameterBag();

        $this->assertSame(
            [
            "fields[MessageItem]" => "a,b,c",
            "sort" => "subject,-date"
            ],
            $parameterBag->toJson()
        );
    }


    /**
     * @param string|null $queryString
     * @return Query
     */
    protected function createTestInstance(string $queryString = null): Query
    {
        return new Query($queryString);
    }


    /**
     * @return string
     */
    protected function getTestedClass(): string
    {
        return Query::class;
    }
}
