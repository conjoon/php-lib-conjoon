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

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Core\Validation\Rule as ValidationRule;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\ParameterRule;
use stdClass;
use Tests\TestCase;

/**
 * Tests ParameterRule.
 */
class ParameterRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = $this->createMockForAbstract(ValidationRule::class);
        $this->assertInstanceOf(ValidationRule::class, $rule);
    }

    /**
     * test isValid() throwing UnexpectedTypeException
     */
    public function testisValidWithUnexpectedTypeException()
    {
        $rule = $this->createMockForAbstract(ParameterRule::class);
        $this->expectException(UnexpectedTypeException::class);

        $rule->isValid(new stdClass(), new ValidationErrors());
    }

    /**
     * test isValid() delegating to validate
     */
    public function testIsValid()
    {
        $parameter = $this->getMockBuilder(Parameter::class)->disableOriginalConstructor()->getMock();
        $errors = new ValidationErrors();

        $rule = $this->createMockForAbstract(ParameterRule::class, ["validate"]);
        $rule->expects($this->once())->method("validate")->with($parameter, $errors)->willReturn(true);

        $this->assertSame(true, $rule->isValid($parameter, $errors));
    }
}
