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

namespace Tests\Conjoon\Web\Validation\Parameter;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\Rule\NamedParameterRule;
use Conjoon\Web\Validation\Parameter\Rule\ParameterRule;
use Conjoon\Web\Validation\Parameter\Rule\ValueInWhitelistRule;
use Tests\TestCase;

/**
 * Tests ValueInWhitelistRule.
 */
class ValueInWhitelistRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $rule = new ValueInWhitelistRule("name", []);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame("name", $parameterName->getValue($rule));
        $this->assertInstanceOf(NamedParameterRule::class, $rule);

        // make sure passing array is okay
        $rule = new ValueInWhitelistRule(["name"], []);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame(["name"], $parameterName->getValue($rule));
    }


    /**
     * tests getWhitelist
     */
    public function testGetWhitelist(): void
    {
        $whitelist = ["-1", "all"];

        $rule = new ValueInWhitelistRule("name", $whitelist);
        $this->assertEquals($whitelist, $rule->getWhitelist());
    }

    /**
     * tests isParameterValueValid()
     */
    public function testIsParameterValueValid(): void
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
    public function testValidate(): void
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

        /**
         * @var ValidationError $err
         */
        $err = $errors[0];
        $this->assertStringContainsString(
            "parameter \"parameter\"'s value must validate against",
            $err->getDetails()
        );
        $this->assertSame(400, $err->getCode());
        $this->assertSame($parameter, $err->getSource());
    }
}
