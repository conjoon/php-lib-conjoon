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

use Conjoon\Http\Query\Exception\UnexpectedQueryParameterException;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\Http\Query\Validation\Parameter\ValuesInWhitelistRule;
use Conjoon\Http\Query\Validation\Validator as HttpQueryValidator;
use Conjoon\Http\Query\Validation\Query\ParameterNamesInListQueryRule;
use Conjoon\JsonApi\Resource\ObjectDescriptionList;
use Conjoon\JsonApi\Validation\Parameter\FieldsetRule;
use Conjoon\JsonApi\Validation\Parameter\IncludeRule;
use Conjoon\JsonApi\Validation\Validator;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Resource\ObjectDescription;
use ReflectionException;
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
     * tests getAvailableSortFields
     */
    public function testGetAvailableSortFields()
    {
        $validator = new Validator();
        $getAvailableSortFields = $this->makeAccessible($validator, "getAvailableSortFields");
        $resourceTarget = $this->createMockForAbstract(
            ObjectDescription::class,
            [
                "getType", "getFields", "getAllRelationshipResourceDescriptions"
            ]
        );

        $resourceTargetChild = $this->createMockForAbstract(
            ObjectDescription::class,
            ["getFields", "getType"]
        );
        $resourceTarget->expects($this->once())->method("getType")->willReturn("MessageItem");
        $resourceTarget->expects($this->exactly(2))->method("getFields")->willReturn(
            ["subject", "date", "size"]
        );

        $resourceTargetChild->expects($this->once())->method("getType")->willReturn("MailFolder");
        $resourceTargetChild->expects($this->once())->method("getFields")->willReturn(
            ["unreadMessages", "totalMessages"]
        );

        $resourceTargetList = new ObjectDescriptionList();
        $resourceTargetList[] = $resourceTarget;
        $resourceTargetList[] = $resourceTargetChild;

        $resourceTarget->expects($this->once())
            ->method("getAllRelationshipResourceDescriptions")
            ->with(true)
            ->willReturn($resourceTargetList);


        $sortFields = [
            "subject", "date", "size", "-subject", "-date", "-size",
            "MessageItem.subject", "MessageItem.date", "MessageItem.size",
            "-MessageItem.subject", "-MessageItem.date", "-MessageItem.size",
            "MailFolder.unreadMessages", "MailFolder.totalMessages", "-MailFolder.unreadMessages",
            "-MailFolder.totalMessages"
        ];

        $this->assertEqualsCanonicalizing(
            $sortFields,
            $getAvailableSortFields->invokeArgs($validator, [$resourceTarget])
        );
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
        $includes = ["MessageItem", "MailFolder"];
        $includeParameter = new Parameter("include", "MessageItem,MailFolder");
        $sort = ["date", "subject", "size"];
        $sortParameter = new Parameter("sort", "-date,subject");
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
        $query->expects($this->exactly(2))
              ->method("getParameter")
              ->withConsecutive(["include"], ["sort"])
              ->willReturnOnConsecutiveCalls($includeParameter, $sortParameter);

        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->willReturn(
            $whitelist
        );

        $resourceTarget->expects($this->once())
                       ->method("getAllRelationshipResourceDescriptions")
                       ->with(true)
                       ->willReturn($objectDescriptionList);

        $validator = $this->createMockForAbstract(Validator::class, ["getAvailableSortFields"]);
        $validator->expects($this->once())->method("getAvailableSortFields")->willReturn($sort);

        $rules = $validator->getParameterRules($query);

        $this->assertSame(3, count($rules));
        $this->assertInstanceOf(ValuesInWhitelistRule::class, $rules[0]);
        $this->assertInstanceOf(IncludeRule::class, $rules[1]);
        $this->assertInstanceOf(FieldsetRule::class, $rules[2]);
        $this->assertSame($sort, $rules[0]->getWhitelist());
        $this->assertSame($whitelist, $rules[1]->getWhitelist());
        $this->assertSame($objectDescriptionList, $rules[2]->getResourceObjectDescriptions());
        $this->assertSame($includes, $rules[2]->getIncludes());
    }
}
