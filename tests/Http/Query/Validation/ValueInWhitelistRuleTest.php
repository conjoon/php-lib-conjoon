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
use Conjoon\Http\Query\Validation\ValueInWhitelistRule;
use stdClass;
use Tests\TestCase;

/**
 * Tests ValueInListParameterRule.
 */
class ValueInWhitelistRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new ValueInWhitelistRule("name", []);
        $this->assertInstanceOf(ParameterRule::class, $rule);
    }


    /**
     * tests supports()
     */
    public function testSupports()
    {
        $rule = new ValueInWhitelistRule("name", []);

        $this->assertTrue(
            $rule->supports(new Parameter("name", "value"))
        );
        $this->assertFalse(
            $rule->supports(new Parameter("unknown", "value"))
        );
        $this->assertFalse(
            $rule->supports(new stdClass())
        );
    }


    /**
     * tests getWhitelist
     */
    public function testGetWhitelist()
    {
        $whitelist = [-1, "all"];

        $rule = new ValueInWhitelistRule("name", $whitelist);
        $this->assertEquals($whitelist, $rule->getWhitelist());
    }

    /**
     * tests isParameterValueValid()
     */
    public function testIsParameterValueValid()
    {
        $rule = new ValueInWhitelistRule("name", []);
        $isParameterValueValid = $this->makeAccessible($rule, "isParameterValueValid");

        $this->assertTrue($isParameterValueValid->invokeArgs(
            $rule,
            [new Parameter("valid", "value"), ["value"]]
        ));

        $this->assertFalse($isParameterValueValid->invokeArgs(
            $rule,
            [new Parameter("valid", "invalid"), ["value"]]
        ));
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $rule = $this->getMockBuilder(ValueInWhitelistRule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getWhitelist", "isParameterValueValid"])
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

        $rule->expects($this->exactly(2))
             ->method("isParameterValueValid")
             ->withConsecutive([$parameter, $validValues], [$parameter, $invalidValues])
             ->willReturnOnConsecutiveCalls(true, false);

        $parameter->expects($this->exactly(1))
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
            "parameter \"parameter\"'s value must validate against",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }
}
