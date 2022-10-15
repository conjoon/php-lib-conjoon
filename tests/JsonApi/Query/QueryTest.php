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

namespace Tests\Conjoon\JsonApi\Query;

use Conjoon\Http\Query\ParameterList;
use Conjoon\Core\Data\Resource\ObjectDescription;
use Conjoon\JsonApi\Query\Query;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\Http\Query\Parameter;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests JsonApi's Query implementation.
 */
class QueryTest extends TestCase
{
    use StringableTestTrait;

    /**
     * Class functionality
     */
    public function testClass()
    {
        $resourceDescription = $this->createMockForAbstract(ObjectDescription::class);

        $parameter = new Parameter("parameter_name", "parameter_value");
        $parameterList = new ParameterList();
        $parameterList[] = $parameter;

        $query = $this->createMockForAbstract(HttpQuery::class, [
            "getParameter", "getAllParameters", "getAllParameterNames", "toString", "getSource", "getName"
        ]);
        $jsonApiQuery = new Query($query, $resourceDescription);

        $query->expects($this->once())->method("getParameter")->with("parameter_name")->willReturn($parameter);
        $query->expects($this->once())->method("getAllParameters")->willReturn($parameterList);
        $query->expects($this->once())->method("getAllParameterNames")->willReturn(["parameter_name"]);
        $query->expects($this->once())->method("getName")->willReturn("query_name");
        $query->expects($this->any())->method("toString")->willReturn("query_to_string");

        $this->assertInstanceOf(HttpQuery::class, $jsonApiQuery);
        $this->assertSame($resourceDescription, $jsonApiQuery->getResourceTarget());

        $this->assertEquals($parameter, $jsonApiQuery->getParameter("parameter_name"));
        $this->assertSame($parameterList, $jsonApiQuery->getAllParameters());
        $this->assertEquals(["parameter_name"], $jsonApiQuery->getAllParameterNames());

        $this->assertSame("query_name", $jsonApiQuery->getName());
        $this->assertSame("query_to_string", $jsonApiQuery->toString());
        $this->assertSame($jsonApiQuery, $jsonApiQuery->getSource());

        $this->assertSame([
            "query" => $jsonApiQuery->toString()
        ], $jsonApiQuery->toArray());
    }

    /**
     * Tests toString()
     */
    public function testToString()
    {
        $this->runToStringTest(Query::class);
    }
}
