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

namespace Tests\Conjoon\Illuminate\Http\Query;

use Conjoon\Http\Query\Exception\InvalidParameterResourceException;
use Conjoon\Illuminate\Http\Query\LaravelQuery;
use Conjoon\Http\Query\Query;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Tests LaravelQuery.
 */
class LaravelQueryTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $query = new LaravelQuery(new Request());

        $this->assertInstanceOf(Query::class, $query);
    }


    /**
     * Tests constructor() with InvalidParameterResourceException
     *
     */
    public function testConstructorException()
    {
        $this->expectException(InvalidParameterResourceException::class);
        $query = new LaravelQuery([]);
    }

    /**
     * tests getParameter()
     * @return void
     */
    public function testGetParameter()
    {
        $query = new LaravelQuery(new Request(["parameter" => "value"]));

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
    public function testGetParameterWithBrackets()
    {
        $query = new LaravelQuery(new Request([
            "fields" => ["A" => "valueA", "B" => "valueB"]
        ]));

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
        $query = new LaravelQuery(new Request());
        $this->assertEquals([], $query->getAllParameterNames());

        $query = new LaravelQuery(new Request([
            "C" => "D", "fields" => ["A" => "valueA", "B" => "valueB"]
        ]));

        $this->assertEquals(["C", "fields[A]", "fields[B]"], $query->getAllParameterNames());
    }


    /**
     * tests getAllParameters()
     * @return void
     */
    public function testGetAllParameters()
    {
        $query = new LaravelQuery(new Request());
        $this->assertEquals([], $query->getAllParameters()->toArray());

        $query = new LaravelQuery(new Request([
            "C" => "D", "fields" => ["A" => "valueA", "B" => "valueB"]
        ]));

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
    public function testGetSource()
    {
        $query = new LaravelQuery(new Request());
        $this->assertSame($query, $query->getSource());
    }


    /**
     * tests toString() and getName()
     * @return void
     */
    public function testToStringAndGetName()
    {
        $request = $this->getMockBuilder(Request::class)->onlyMethods(["getQueryString"])->getMock();


        $request->expects($this->exactly(2))->method("getQueryString")->willReturn("query=string");

        $query = new LaravelQuery($request);

        $this->assertSame($request->getQueryString(), $query->toString());
        $this->assertSame($query->toString(), $query->getName());
    }
}
