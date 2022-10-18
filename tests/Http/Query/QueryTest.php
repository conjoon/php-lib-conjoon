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

namespace Tests\Conjoon\Http\Query;

use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Http\Query\ParameterTrait;
use Conjoon\Http\Query\Query;
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
    public function testClass()
    {
        $query = new Query();

        $uses = class_uses(Query::class);
        $this->assertContains(ParameterTrait::class, $uses);

        $this->assertInstanceOf(Query::class, $query);
    }


    /**
     * tests getParameter()
     * @return void
     */
    public function testGetParameter()
    {
        $query = new Query("parameter=value");

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
        $query = new Query(
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
    public function testGetAllParameterNames()
    {
        $query = new Query(null, []);
        $this->assertEquals([], $query->getAllParameterNames());

        $query = new Query(
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
        $query = new Query();
        $this->assertEquals([], $query->getAllParameters()->toArray());

        $query = new Query(
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
        $query = new Query();
        $this->assertSame($query, $query->getSource());
    }


    /**
     * tests toString() and getName() and toArray()
     * @return void
     */
    public function testToStringAndGetNameAndToArray(): void
    {

        $query = new Query("query=string");

        $this->assertSame("query=string", $query->toString());
        $this->assertSame($query->toString(), $query->getName());

        $this->assertSame([
            "query" => $query->toString()
        ], $query->toArray());

        $this->runToStringTest(Query::class);
    }
    
    
    /**
     * tests getParameterBag()
     * @return void
     */
    public function testGetParameterBag(): void
    {
        $query = new Query( "fields[MessageItem]=a,b,c&sort=subject,-date");

        $parameterBag = $query->getParameterBag();

        $this->assertSame(
            [
            "fields[MessageItem]" => "a,b,c",
            "sort" => "subject,-date"
            ],
            $parameterBag->toJson()
        );
    }
}
