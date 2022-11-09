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

use Conjoon\Data\Validation\Rule as ValidationRule;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Exception\UnexpectedQueryParameterException;
use Conjoon\Web\Validation\Parameter\ParameterRule;
use PHPUnit\Framework\MockObject\MockObject;
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
    public function testClass(): void
    {
        /**
         * @var MockObject&ParameterRule $rule
         */
        $rule = $this->createMockForAbstract(ParameterRule::class);
        $this->assertInstanceOf(ValidationRule::class, $rule);
    }


    /**
     * test supports()
     */
    public function testSupports(): void
    {
        /**
         * @var MockObject&ParameterRule $rule
         */
        $rule = $this->createMockForAbstract(ParameterRule::class);

        $this->assertTrue($rule->supports(new Parameter("name", "value")));
        $this->assertFalse($rule->supports(new stdClass()));
    }


    /**
     * test isValid() throwing UnexpectedQueryParameterException
     */
    public function testIsValidWithUnexpectedQueryParameterException(): void
    {
        $parameter = new Parameter("include", "none");

        /**
         * @var MockObject&ParameterRule $rule
         */
        $rule = $this->createMockForAbstract(ParameterRule::class, ["supports"]);
        $rule->expects($this->once())
             ->method("supports")
             ->with($parameter)
             ->willReturn(false);

        $this->expectException(UnexpectedQueryParameterException::class);


        $rule->isValid($parameter, new ValidationErrors());
    }

    /**
     * test isValid() delegating to validate
     */
    public function testIsValid(): void
    {
        $parameter = $this->getMockBuilder(Parameter::class)->disableOriginalConstructor()->getMock();
        $errors = new ValidationErrors();

        /**
         * @var ParameterRule&MockObject $rule
         */
        $rule = $this->createMockForAbstract(ParameterRule::class, ["supports", "validate"]);
        $rule->expects($this->once())
             ->method("validate")
             ->with($parameter, $errors)
             ->willReturn(true);

        $rule->expects($this->once())
             ->method("supports")
             ->with($parameter)
             ->willReturn(true);

        $this->assertSame(true, $rule->isValid($parameter, $errors));
    }
}
