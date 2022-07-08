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

namespace Tests\Conjoon\Http\Query\Validation;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Query;
use Conjoon\Http\Query\Validation\ParameterNamesInListQueryRule;
use Conjoon\Http\Query\Validation\ParameterRule;
use Conjoon\Http\Query\Validation\QueryRule;
use Conjoon\Http\Query\Validation\ValueInListParameterRule;
use Tests\TestCase;

/**
 * Tests ValueInListParameterRule.
 */
class ValueInListParameterRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new ValueInListParameterRule([]);
        $this->assertInstanceOf(ParameterRule::class, $rule);
    }

    public function testGetWhiteList()
    {
        $whitelist = [-1, "all"];

        $rule = new ValueInListParameterRule($whitelist);
        $this->assertEquals($whitelist, $rule->getWhitelist());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $rule = $this->getMockBuilder(ValueInListParameterRule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getWhiteList"])
            ->getMock();
        $validate = $this->makeAccessible($rule, "validate");

        $errors = new ValidationErrors();

        $parameter = $this->getMockBuilder(Parameter::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(["getName", "getValue"])
                      ->getMock();

        $validValues = ["include", "MailAccount", "MailFolder"];
        $invalidValues = ["include", "MailAccount", "MessageItem"];

        $value = "MailFolder";

        $rule->expects($this->exactly(2))
            ->method("getWhitelist")
            ->willReturnOnConsecutiveCalls($validValues, $invalidValues);

        $parameter->expects($this->exactly(2))
            ->method("getValue")
            ->willReturn(
                $value
            );
        $parameter->expects($this->once())
            ->method("getName")
            ->willReturn("parameter");

        $this->assertTrue(
            $validate->invokeArgs($rule, [$parameter, $errors])
        );
        $this->assertFalse($errors->hasError());
        $this->assertFalse(
            $validate->invokeArgs($rule, [$parameter, $errors])
        );
        $this->assertTrue($errors->hasError());
        $this->assertStringContainsString(
            "parameter \"parameter\" must be one of",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }
}
