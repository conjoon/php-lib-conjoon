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

namespace Tests\Conjoon\Http\Query\Validation\Query;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Query;
use Conjoon\Http\Query\Validation\Query\OnlyParameterNamesRule;
use Conjoon\Http\Query\Validation\Query\QueryRule;
use Tests\TestCase;

/**
 * Tests OnlyParameterNamesRule.
 */
class OnlyParameterNamesRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new OnlyParameterNamesRule([]);
        $this->assertInstanceOf(QueryRule::class, $rule);
    }

    public function testGetWhiteList()
    {
        $whitelist = ["include", "filter"];

        $rule = new OnlyParameterNamesRule($whitelist);
        $this->assertEquals($whitelist, $rule->getWhitelist());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $rule = $this->getMockBuilder(OnlyParameterNamesRule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getWhiteList"])
            ->getMock();
        $validate = $this->makeAccessible($rule, "validate");

        $errors = new ValidationErrors();

        $query = $this->createMockForAbstract(Query::class, ["getAllParameterNames"]);

        $validParameterNames = ["include", "fields[MailAccount]", "fields[MailFolder]"];
        $invalidParameterNames = ["include", "fields[MailAccount]", "fields[MessageItem]"];

        $parameterNames = ["include", "fields[MailAccount]", "fields[MailFolder]"];

        $rule->expects($this->exactly(2))
            ->method("getWhitelist")
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
