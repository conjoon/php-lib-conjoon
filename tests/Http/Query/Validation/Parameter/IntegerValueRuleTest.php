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

namespace Tests\Conjoon\Http\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Parameter\IntegerValueRule;
use Conjoon\Http\Query\Validation\Parameter\NamedParameterRule;
use InvalidArgumentException;
use stdClass;
use Tests\TestCase;

/**
 * Tests IntegerValueRuleTest.
 */
class IntegerValueRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new IntegerValueRule("name");
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame("name", $parameterName->getValue($rule));
        $this->assertInstanceOf(NamedParameterRule::class, $rule);
    }

    /**
     * test wrong operator throws InvalidArgumentException
     */
    public function testConstructorWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        new IntegerValueRule("name", "/");
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $errors = new ValidationErrors();

        // simple validate type
        $rule = new IntegerValueRule("limit");
        $this->assertFalse($rule->isValid(new Parameter("limit", "string_value"), $errors));
        $this->assertStringContainsString("cannot be treated as integer", $errors->peek()->getDetails());

        $rule = new IntegerValueRule("limit");
        $this->assertTrue($rule->isValid(new Parameter("limit", "1234"), $errors));

        $rule = new IntegerValueRule("limit");
        $this->assertFalse($rule->isValid(new Parameter("limit", "01234"), $errors));

        // equality
        $rule = new IntegerValueRule("limit", "=", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "1"), $errors));
        $this->assertFalse($rule->isValid(new Parameter("limit", "123"), $errors));
        $this->assertStringContainsString("is not = 1", $errors->peek()->getDetails());

        // greater than
        $rule = new IntegerValueRule("limit", ">", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "2"), $errors));
        $this->assertFalse($rule->isValid(new Parameter("limit", "-1"), $errors));

        // greater than or equal to
        $rule = new IntegerValueRule("limit", ">=", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "2"), $errors));
        $this->assertTrue($rule->isValid(new Parameter("limit", "1"), $errors));

        // less than
        $rule = new IntegerValueRule("limit", "<", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "0"), $errors));
        $this->assertFalse($rule->isValid(new Parameter("limit", "2"), $errors));

        // less than or equal to
        $rule = new IntegerValueRule("limit", "<=", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "-2"), $errors));
        $this->assertTrue($rule->isValid(new Parameter("limit", "1"), $errors));

        // not equal to
        $rule = new IntegerValueRule("limit", "!=", 1);
        $this->assertTrue($rule->isValid(new Parameter("limit", "-2"), $errors));
        $this->assertFalse($rule->isValid(new Parameter("limit", "1"), $errors));
    }
}
