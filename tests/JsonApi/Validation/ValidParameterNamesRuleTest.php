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

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\JsonApi\Query;
use Conjoon\JsonApi\Resource\ObjectDescription;
use Conjoon\JsonApi\Validation\Rule;
use Conjoon\JsonApi\Validation\ValidParameterNamesRule;
use Tests\TestCase;

/**
 * Tests ValidParameterNamesRule
 */
class ValidParameterNamesRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new ValidParameterNamesRule();
        $this->assertInstanceOf(Rule::class, $rule);
    }


    /**
     * tests getAllowedParameterNames()
     */
    public function testGetAllowedParameterNames()
    {
        $rule = new ValidParameterNamesRule();
        $getAllowedParameterNames = $this->makeAccessible($rule, "getAllowedParameterNames");

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
        ], $getAllowedParameterNames->invokeArgs($rule, [$query]));
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $rule = $this->getMockBuilder(ValidParameterNamesRule::class)
                     ->onlyMethods(["getAllowedParameterNames"])
                     ->getMock();
        $validate = $this->makeAccessible($rule, "validate");

        $errors = new ValidationErrors();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getAllParameterNames"])->getMock();

        $validParameterNames = ["include", "fields[MailAccount]", "fields[MailFolder]"];
        $invalidParameterNames = ["include", "fields[MailAccount]", "fields[MessageItem]"];

        $parameterNames = ["include", "fields[MailAccount]", "fields[MailFolder]"];

        $rule->expects($this->exactly(2))
             ->method("getAllowedParameterNames")
             ->willReturnOnConsecutiveCalls($validParameterNames, $invalidParameterNames);

        $query->expects($this->exactly(2))
              ->method("getAllParameterNames")
              ->willReturn(
                  $parameterNames
              );

        $this->assertTrue(
            $validate->invokeArgs($rule, [$query, $errors])
        );
        $this->assertFalse($errors->hasError());
        $this->assertFalse(
            $validate->invokeArgs($rule, [$query, $errors])
        );
        $this->assertTrue($errors->hasError());
        $this->assertStringContainsString("found additional parameters", $errors[0]->getDetails());
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($query, $errors[0]->getSource());
    }
}
