<?php

/**
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

namespace Conjoon\JsonApi\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Parameter\JsonEncodedRule;

/**
 * This parameter rule supports validation of filters provided as json encoded objects containing
 * filter rules in a polish notation, where the keys are operators and/or functions, and the values
 * subsequent filters or filter arguments.
 *
 * The following filter query parameter requests the resource collection to only contain the resource data
 * with the id "1":
 * ?filter={"=": {"id": 1]}
 *
 * The following requests data marked as "recent", or data marked as "unseen" and with an id >= 1657:
 * ?filter={"OR":{"=":{"recent":true}, "AND": {"=": "unseen": true}, ">=": {"id":1657}}
 *
 * This rule applies for parameters named "filter"
 */
class PnFilterRule extends JsonEncodedRule
{
    /**
     * @var array
     */
    protected array $attributes;


    /**
     * Constructor.
     *
     * @param array $attributes The list of valid attributes this rule considers.
     */
    public function __construct(array $attributes)
    {
        parent::__construct("filter");

        $this->attributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function validate(Parameter $parameter, ValidationErrors $errors): bool
    {
        if (!parent::validate($parameter, $errors)) {
            return false;
        }

        $value = json_decode($parameter->getValue(), true);
        $error = "";

        $parse = function ($value) use (&$parse) {


            foreach ($value as $operator => $filter) {
                if (!$this->isValidOperator($operator)) {
                    return "\"$operator\" is not a valid operator";
                }

                if (!$this->isLogicalOperator($operator)) {
                    $attributes = array_keys($filter);
                    $diff = array_values(array_diff($attributes, $this->getAttributes()));
                    if (count($diff) > 0) {
                        return "\"$operator\" needs a valid attribute argument, received \"" . implode("\", \"", $diff) . "\"";
                    }
                    continue;
                }

                //{"AND" : {"=": ..., ">=" : ...}}
                if ($this->isLogicalOperator($operator)) {
                    $rules = array_keys($filter);

                    if (count($rules) <= 1) {
                        return "Logical operator \"$operator\" " .
                            "expects at least 2 operands, " . count($rules) . " given";
                    }
                }

                return $parse($filter);
            }

            return true;
        };

        $error = $parse($value);

        if (is_string($error)) {
            $errors[] = new ValidationError(
                $parameter,
                $error,
                400
            );

            return false;
        }

        return true;
    }


    /**
     * Returns true if the passed argument is a valid operator or function.
     *
     * @param string $value
     * @return bool
     */
    public function isValidOperator(string $value): bool
    {
        return in_array($value, $this->getRelationalOperators()) ||
               $this->isLogicalOperator($value) ||
               in_array($value, $this->getFunctions());
    }


    /**
     * Returns true if the submitted argument is a logical operator according to this implementation.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isLogicalOperator(string $value): bool
    {
        return in_array($value, $this->getLogicalOperators());
    }


    /**
     * Returns true if the passed argument is a valid attribute
     *
     * @param string $value
     *
     * @return bool
     */
    public function isAttribute(string $value): bool
    {
        return in_array($value, $this->getAttributes());
    }



    /**
     * Returns the list of relational operators supported with this rule.
     *
     * @return string[]
     */
    public function getRelationalOperators(): array
    {
        return ["<=", "<", "!=", "=", ">", ">="];
    }


    /**
     * Returns the list of logical operators supported with this rule.
     *
     * @return string[]
     */
    public function getLogicalOperators(): array
    {
        return ["AND", "OR"];
    }


    /**
     * Returns the list of functions supported with this rule.
     *
     * @return string[]
     */
    public function getFunctions(): array
    {
        return ["IN"];
    }


    /**
     * Returns all the attribute names this rule considers. The list of rules is dynamic and must be
     * specified with the constructor.
     * @return void
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
