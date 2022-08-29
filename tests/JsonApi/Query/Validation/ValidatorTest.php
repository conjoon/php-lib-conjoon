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

namespace Tests\Conjoon\JsonApi\Query\Validation;

use Conjoon\Http\Query\Exception\UnexpectedQueryParameterException;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\Http\Query\Validation\Validator as HttpQueryValidator;
use Conjoon\Http\Query\Validation\Query\OnlyParameterNamesRule;
use Conjoon\JsonApi\Resource\ObjectDescriptionList;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\JsonApi\Query\Validation\Parameter\IncludeRule;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Resource\ObjectDescription;
use ReflectionException;
use Conjoon\Http\Query\Validation\Query\RequiredParameterNamesRule;
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
     * tests unfold()
     * @throws ReflectionException
     */
    public function testUnfoldWithUnexpectedQueryParameterException()
    {
        $validator = new Validator();
        $unfold = $this->makeAccessible($validator, "unfoldInclude");
        $this->expectException(UnexpectedQueryParameterException::class);

        $parameter = new Parameter("parameter", "");
        $unfold->invokeArgs($validator, [$parameter]);
    }


    /**
     * tests unfold()
     */
    public function testUnfold()
    {
        $validator = new Validator();
        $unfold = $this->makeAccessible($validator, "unfoldInclude");
        $parameter = new Parameter("include", "MailFolder.MailAccount,MailFolder.MailAccount.Server,MailFolder");

        $this->assertEquals(
            ["MailFolder", "MailAccount", "Server"],
            $unfold->invokeArgs($validator, [$parameter])
        );

        $parameter = new Parameter("include", "");

        $this->assertEquals(
            [],
            $unfold->invokeArgs($validator, [$parameter])
        );
    }


    /**
     * test getAllowedParameterNames()
     */
    public function testGetAllowedParameterNames()
    {
        $validator = new Validator();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceTarget"])->getMock();

        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class, [
            "getType", "getAllRelationshipPaths"]);
        $query->expects($this->once())->method("getResourceTarget")->willReturn($resourceTarget);

        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->with(true)->willReturn([
            "path", "entity", "path.entity2", "path.entity2.entity3"
        ]);


        $this->assertEqualsCanonicalizing([
            "include",
            "fields[entity]",
            "fields[path]",
            "fields[entity2]",
            "fields[entity3]"
        ], $validator->getAllowedParameterNames($query));
    }


    /**
     * test getRequiredParameterNames()
     */
    public function testGetRequiredParameterNames()
    {
        $validator = new Validator();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->assertEquals(
            [],
            $validator->getRequiredParameterNames($query)
        );
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
        $allowedParameterNames = ["include"];
        $requiredParameterNames = ["include"];

        $validator = $this->getMockBuilder(Validator::class)
                          ->onlyMethods(["getAllowedParameterNames", "getRequiredParameterNames"])
                          ->getMock();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()->getMock();

        $validator->expects($this->once())->method("getAllowedParameterNames")
                  ->with($query)
                  ->willReturn($allowedParameterNames);

        $validator->expects($this->once())->method("getRequiredParameterNames")
            ->with($query)
            ->willReturn($requiredParameterNames);


        $rules = $validator->getQueryRules($query);

        $this->assertSame(2, count($rules));

        $this->assertInstanceOf(OnlyParameterNamesRule::class, $rules[0]);
        $this->assertSame($allowedParameterNames, $rules[0]->getWhitelist());

        $this->assertInstanceOf(RequiredParameterNamesRule::class, $rules[1]);
        $this->assertSame($requiredParameterNames, $rules[1]->getRequired());
    }


    /**
     * test getParameterRules()
     */
    public function testGetParameterRules()
    {
        $includes = ["MessageItem", "MailFolder"];
        $includeParameter = new Parameter("include", "MessageItem,MailFolder");
        $whitelist = ["fields[MessageItem]"];
        $objectDescriptionList = new ObjectDescriptionList();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(["getParameter", "getResourceTarget"])
                      ->getMock();

        $resourceTarget = $this->createMockForAbstract(
            ObjectDescription::class,
            ["getAllRelationshipPaths", "getAllRelationshipResourceDescriptions"]
        );

        $query->expects($this->once())->method("getResourceTarget")->willReturn($resourceTarget);
        $query->expects($this->exactly(1))
              ->method("getParameter")
              ->withConsecutive(["include"])
              ->willReturnOnConsecutiveCalls($includeParameter);

        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->willReturn(
            $whitelist
        );

        $resourceTarget->expects($this->once())
                       ->method("getAllRelationshipResourceDescriptions")
                       ->with(true)
                       ->willReturn($objectDescriptionList);

        $validator = $this->createMockForAbstract(Validator::class, ["getAvailableSortFields"]);

        $rules = $validator->getParameterRules($query);

        $this->assertSame(2, count($rules));
        $this->assertInstanceOf(IncludeRule::class, $rules[0]);
        $this->assertInstanceOf(FieldsetRule::class, $rules[1]);
        $this->assertSame($whitelist, $rules[0]->getWhitelist());
        $this->assertSame($objectDescriptionList, $rules[1]->getResourceObjectDescriptions());
        $this->assertSame($includes, $rules[1]->getIncludes());
    }
}
