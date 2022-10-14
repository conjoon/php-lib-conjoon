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

namespace Tests\Conjoon\JsonApi\Query\Parameter\Validation;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Parameter\JsonEncodedRule;
use Conjoon\JsonApi\Query\Validation\Parameter\PnFilterRule;
use Tests\TestCase;

/**
 * Tests PnFilterRule
 */
class PnFilterRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new PnFilterRule([]);
        $this->assertInstanceOf(JsonEncodedRule::class, $rule);

        $getParameterName = $this->makeAccessible($rule, "parameterName", true);

        $this->assertSame("filter", $getParameterName->getValue($rule));
    }


    /**
     * tests getAttributes
     * @return void
     */
    public function testGetAttributes()
    {
        $attributes = ["field"];
        $rule = new PnFilterRule($attributes);
        $this->assertSame($attributes, $rule->getAttributes());
    }


    /**
     * tests getRelationalOperators()
     *
     * @return void
     */
    public function testGetRelationalOperators()
    {
        $operators = ["<=", "<", "!=", "=", ">", ">=", "=="];
        $rule = new PnFilterRule([]);

        $this->assertEqualsCanonicalizing(
            $operators,
            $rule->getRelationalOperators()
        );
    }


    /**
     * test getFunctions()
     *
     * @return void
     */
    public function testGetFunctions()
    {
        $functions = ["IN"];
        $rule = new PnFilterRule([]);

        $this->assertEqualsCanonicalizing(
            $functions,
            $rule->getFunctions()
        );
    }


    /**
     * tests getLogicalOperators()
     *
     * @return void
     */
    public function testGetLogicalOperators()
    {
        $operators = ["OR", "||"];
        $rule = new PnFilterRule([]);

        $this->assertEqualsCanonicalizing(
            $operators,
            $rule->getLogicalOperators()
        );
    }


    /**
     * @return void
     */
    public function testIsLogicalOperator()
    {
        $operators = ["||", "OR"];

        $rule = new PnFilterRule([]);

        foreach ($operators as $operator) {
            $this->assertTrue($rule->isLogicalOperator($operator));
        }

        $this->assertFalse($rule->isLogicalOperator("foo"));
    }



    /**
     * tests isValid()/validate()
     * @return void
     */
    public function testValidate()
    {
        $errors = new ValidationErrors();
        $fields = ["MessageItem.subject", "subject", "MailFolder.name", "date", "size", "id"];

        $rule = new PnFilterRule($fields);

        $tests = [
            [
                "filter" => ["=" => ["size" => 1000]],
                "result" => true
            ],
            [
                "filter" => ["size" => 1000],
                "result" => "is not a valid operator"
            ],
            [
                "filter" => ["OR" => [[ "==" => ["size" => 1000]], [">" => ["date" => 127000000]]]],
                "result" => true
            ],
            [
                "filter" => ["OR" => [[ "=" => ["size" => 1000]]]],
                "result" => "expects at least 2 operands"
            ],
            [
                "filter" => ["OR" => [ ["=" => ["size" => 1000]], ["IN" => ["subject" => ["Hello World"]]]]],
                "result" => true
            ],
            [
                "filter" => ["OR" => [ ["=" => ["size" => 1000]], [">" => ["IN" => ["subject" => ["Hello World"]]]]]],
                "result" => "needs a valid attribute"
            ],
            [
                "filter" => [
                    "OR" => [
                        ["==" => ["id" => 4]],
                        ["||" => [
                            ["="  => ["subject"  => "Hello World"]],
                            [">=" => ["size"     => 1657]]
                        ]]
                    ]
                ],
                "result" => true
            ]

        ];


        foreach ($tests as $test) {
            $filter = json_encode($test["filter"]);
            $result = $test["result"];

            $parameter = new Parameter("filter", $filter);

            if (is_string($result)) {
                $this->assertFalse($rule->isValid($parameter, $errors));
                $this->assertStringContainsString($result, $errors->peek()->getDetails());
            } else {
                $this->assertTrue($rule->isValid($parameter, $errors));
            }
        }
    }
}
