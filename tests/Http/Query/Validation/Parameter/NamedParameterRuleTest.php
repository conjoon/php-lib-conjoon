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

use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Parameter\NamedParameterRule;
use Conjoon\Http\Query\Validation\Parameter\ParameterRule;
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
    public function testClass()
    {
        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], ["parameter_name"]);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame("parameter_name", $parameterName->getValue($rule));
        $this->assertInstanceOf(ParameterRule::class, $rule);

        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], [["parameter_name"]]);
        $parameterName = $this->makeAccessible($rule, "parameterName", true);
        $this->assertSame(["parameter_name"], $parameterName->getValue($rule));
    }


    /**
     * tests supports()
     */
    public function testSupports()
    {
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

        $rule = $this->createMockForAbstract(NamedParameterRule::class, [], [["name", "second_name"]]);

        $this->assertTrue(
            $rule->supports(new Parameter("name", "value"))
        );
        $this->assertTrue(
            $rule->supports(new Parameter("second_name", "value"))
        );
    }
}
