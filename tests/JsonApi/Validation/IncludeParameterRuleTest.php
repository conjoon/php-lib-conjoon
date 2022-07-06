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
use Conjoon\Http\Query\Parameter;
use Conjoon\JsonApi\Query as JsonApiQuery;
use Conjoon\JsonApi\Resource\ObjectDescription;
use Conjoon\JsonApi\Validation\IncludeParameterRule;
use Conjoon\JsonApi\Validation\Rule;
use Tests\TestCase;

/**
 * Tests IncludeParameterRule
 */
class IncludeParameterRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new IncludeParameterRule();
        $this->assertInstanceOf(Rule::class, $rule);
    }


    /**
     * tests merge()
     */
    public function testMerge()
    {
        $rule = new IncludeParameterRule();
        $merge = $this->makeAccessible($rule, "merge");

        $tests = [
            [
                "includes" => [
                    "MessageItem",
                    "MailFolder",
                    "MailFolder.MessageItem",
                    "MailFolder.MessageItem.Body",
                    "MailFolder.MailAccount"
                ],
                "expected" => [
                    "MessageItem",
                    "MailFolder.MessageItem.Body",
                    "MailFolder.MailAccount"
                ]
            ],
            [
                "includes" => [
                    "MessageItem",
                    "MailFolder"
                ],
                "expected" => [
                    "MessageItem",
                    "MailFolder"
                ]
            ],
            [
                "includes" => [
                    "MessageItem",
                    "MailFolder",
                    "MessageItem.Draft",
                    "MailFolder.MailAccount",
                    "MailFolder.MessageItem.Body",
                    "MailFolder.MailAccount.Item.Envelope"
                ],
                "expected" => [
                    "MessageItem.Draft",
                    "MailFolder.MessageItem.Body",
                    "MailFolder.MailAccount.Item.Envelope"
                ]
            ],
        ];


        foreach ($tests as $test) {
            $this->assertEquals(
                $test["expected"],
                $merge->invokeArgs($rule, [$test["includes"]])
            );
        }
    }


    /**
     * tests parse()
     */
    public function testParse()
    {
        $rule = new IncludeParameterRule();
        $merge = $this->makeAccessible($rule, "parse");

        $this->assertEquals(
            [],
            $merge->invokeArgs($rule, [""])
        );

        $this->assertEquals(
            ["MailAccount", "MailFolder"],
            $merge->invokeArgs($rule, ["MailAccount,MailFolder"])
        );
    }


    /**
     * tests unfold()
     */
    public function testUnfold()
    {
        $rule = new IncludeParameterRule();
        $unfold = $this->makeAccessible($rule, "unfold");

        $includes = [
            "MailFolder.MailAccount",
            "MailFolder.MailAccount.Server",
            "MailFolder"
        ];

        $this->assertEquals(
            ["MailFolder", "MailAccount", "Server"],
            $unfold->invokeArgs($rule, [$includes])
        );
    }


    /**
     * tests validate
     */
    public function testValidate()
    {
        $rule = new IncludeParameterRule();
        $validate = $this->makeAccessible($rule, "validate");

        $errors = new ValidationErrors();

        $query = $this->getMockBuilder(JsonApiQuery::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(["getParameter", "getResourceTarget"])->getMock();

        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class, ["getAllRelationshipPaths"]);
        $resourceTarget->expects($this->exactly(2))->method("getAllRelationshipPaths")->willReturn(
            [
                "MessageItem",
                "MailFolder",
                "MailFolder.MailAccount",
                "MailFolder.MailAccount.ServerSettings"
            ]
        );

        $validIncludeParameter = new Parameter("include", "MessageItem,MailFolder.MailAccount.ServerSettings");
        $invalidIncludeParameter = new Parameter("include", "ServerSettings,MailFolder");

        $query->expects($this->exactly(2))->method("getParameter")->with("include")->willReturnOnConsecutiveCalls(
            $validIncludeParameter,
            $invalidIncludeParameter
        );
        $query->expects($this->exactly(2))->method("getResourceTarget")->willReturn($resourceTarget);

        $this->assertTrue(
            $validate->invokeArgs($rule, [$query, $errors])
        );
        $this->assertFalse($errors->hasError());
        $this->assertFalse(
            $validate->invokeArgs($rule, [$query, $errors])
        );
        $this->assertTrue($errors->hasError());
        $this->assertStringContainsString("parameter \"include\" must only contain one of ", $errors[0]->getDetails());
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($invalidIncludeParameter, $errors[0]->getSource());
    }
}
