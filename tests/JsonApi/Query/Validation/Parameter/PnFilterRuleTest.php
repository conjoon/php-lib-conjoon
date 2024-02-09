<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\JsonApi\Query\Parameter\Validation;

use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonApi\Query\Validation\Parameter\PnFilterRule;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\Rule\JsonEncodedRule;
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
        $operators = ["OR", "||", "AND", "&&"];
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
            ],
            [
                "filter" => [
                    "AND" => [
                        ["==" => ["id" => 4]],
                        ["="  => ["subject"  => "Hello World"]]
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
