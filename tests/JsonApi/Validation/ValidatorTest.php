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

namespace Tests\Conjoon\JsonApi\Validation;

use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\Http\Query\Validation\Validator as HttpQueryValidator;
use Conjoon\Http\Query\Validation\ParameterNamesInListQueryRule;
use Conjoon\JsonApi\Validation\IncludeParameterRule;
use Conjoon\JsonApi\Validation\Validator;
use Conjoon\JsonApi\Query;
use Conjoon\JsonApi\Resource\ObjectDescription;
use Tests\TestCase;

/**
 * Tests Validator.
 */
class ValidatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $validator = new Validator();
        $this->assertInstanceOf(HttpQueryValidator::class, $validator);
    }

    /**
     * test getValidParameterNamesForQuery()
     */
    public function testGetValidParameterNamesForQuery()
    {
        $validator = new Validator();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceTarget"])->getMock();

        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class, [
            "getType", "getAllRelationshipPaths"]);
        $query->expects($this->once())->method("getResourceTarget")->willReturn($resourceTarget);

        $resourceTarget->expects($this->once())->method("getType")->willReturn("entity");
        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->willReturn([
            "path", "path.entity2"
        ]);


        $this->assertEquals([
            "include",
            "fields[entity]",
            "fields[path]",
            "fields[path.entity2]"
        ], $validator->getValidParameterNamesForQuery($query));
    }


    /**
     * test supports()
     */
    public function testSupports()
    {
        $validator = new Validator();

        $this->assertTrue(
            $validator->supports(
                $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock()
            )
        );


        $this->assertFalse(
            $validator->supports(
                $this->getMockBuilder(HttpQuery::class)->disableOriginalConstructor()->getMock()
            )
        );
    }


    /**
     * test getQueryRules()
     */
    public function testGetQueryRules()
    {
        $parameterNames = ["include"];

        $validator = $this->getMockBuilder(Validator::class)
                          ->onlyMethods(["getValidParameterNamesForQuery"])
                          ->getMock();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()->getMock();

        $validator->expects($this->once())->method("getValidParameterNamesForQuery")
                  ->with($query)
                  ->willReturn($parameterNames);


        $rules = $validator->getQueryRules($query);

        $this->assertSame(1, count($rules));
        $this->assertInstanceOf(ParameterNamesInListQueryRule::class, $rules[0]);
        $this->assertSame($parameterNames, $rules[0]->getWhitelist());
    }


    /**
     * test getParameterRules()
     */
    public function testGetParameterRules()
    {
        $whitelist = ["fields[MessageItem]"];

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(["getResourceTarget"])
                      ->getMock();

        $resourceTarget = $this->createMockForAbstract(
            ObjectDescription::class,
            ["getAllRelationshipPaths"]
        );
        $query->expects($this->once())->method("getResourceTarget")->willReturn($resourceTarget);

        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->willReturn(
            $whitelist
        );

        $validator = new Validator();

        $rules = $validator->getParameterRules($query);

        $this->assertSame(1, count($rules));
        $this->assertInstanceOf(IncludeParameterRule::class, $rules[0]);
        $this->assertSame($whitelist, $rules[0]->getWhitelist());
    }
}
