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

use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\ParameterRule;
use Conjoon\Web\Validation\Parameter\Rule\NamedParameterRule;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\TestCase;

/**
 * Tests NamedParameterRule.
 */
class NamedParameterRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        /**
         * @var NamedParameterRule&MockObject $rule
         */
        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], ["parameter_name"]);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame("parameter_name", $parameterName->getValue($rule));
        $this->assertInstanceOf(ParameterRule::class, $rule);

        /**
         * @var NamedParameterRule&MockObject $rule
         */
        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], [["parameter_name"]]);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame(["parameter_name"], $parameterName->getValue($rule));
    }


    /**
     * tests supports()
     */
    public function testSupports(): void
    {
        /**
         * @var NamedParameterRule&MockObject $rule
         */
        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], ["name"]);

        $this->assertTrue(
            $rule->supports(new Parameter("name", "value"))
        );
        $this->assertFalse(
            $rule->supports(new Parameter("unknown", "value"))
        );
        $this->assertFalse(
            $rule->supports(new stdClass())
        );

        /**
         * @var NamedParameterRule&MockObject $rule
         */
        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], [["name", "second_name"]]);

        $this->assertTrue(
            $rule->supports(new Parameter("name", "value"))
        );
        $this->assertTrue(
            $rule->supports(new Parameter("second_name", "value"))
        );
    }
}
