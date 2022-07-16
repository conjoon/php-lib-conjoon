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

namespace Conjoon\Http\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Http\Query\Parameter;
use Conjoon\Core\Validation\ValidationErrors;
use InvalidArgumentException;

/**
 * Class providing functionality for validating a parameter that should be treated as
 * integer value.
 * Optional operators allow for configuring validation behavior.
 *
 * @example
 *
 *    $errors = new ValidationErrors();
 *
 *    // simple validate type
 *   $typeRule = new IntegerValueRule("limit");
 *   $noInt = new Parameter("limit", "string_value");
 *   $typeRule->isValid(new Parameter("limit", "string_value"), $errors); // false
 *
 *   // equality
 *   $equalsRule = new IntegerValueRule("limit", "=", 1);
 *   $rule->isValid(new Parameter("limit", "1"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "123"), $errors); // false
 *
 *   // greater than
 *   $equalsRule = new IntegerValueRule("limit", ">", 1);
 *   $rule->isValid(new Parameter("limit", "2"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "-1"), $errors); // false
 *
 *   // greater than or equal to
 *   $equalsRule = new IntegerValueRule("limit", ">=", 1);
 *   $rule->isValid(new Parameter("limit", "2"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "1"), $errors); // true
 *
 *   // less than
 *   $equalsRule = new IntegerValueRule("limit", "<", 1);
 *   $rule->isValid(new Parameter("limit", "0"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "2"), $errors); // false
 *
 *   // less than or equal to
 *   $equalsRule = new IntegerValueRule("limit", "<=", 1);
 *   $rule->isValid(new Parameter("limit", "-2"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "1"), $errors); // true
 *
 *   // not equal to
 *   $equalsRule = new IntegerValueRule("limit", "!=", 1);
 *   $rule->isValid(new Parameter("limit", "-2"), $errors); // true
 *   $rule->isValid(new Parameter("limit", "1"), $errors); // false
 *
 */
class IntegerValueRule extends NamedParameterRule
{
    /**
     * @var string|null
     */
    protected ?string $operator = null;

    /**
     * @var array
     */
    protected $validOperators = ["=", "!=", "<", ">", "<=", ">="];

    /**
     * @var int|null
     */
    protected ?int $value = null;

    /**
     * Constructor.
     * If either the operator or the value is omitted, this validator will simply validate the type.
     *
     * @param string $parameterName The name of the parameter for which this rule should be applied
     * @param string|null $operator Any of =, !=, <, >, <= or >=
     * @param int|null $value The value that should be considered with the operator
     *
     * @throws InvalidArgumentException if operator is not null and not in the list of valid operators,
     * or if $value is not null and not an integer
     */
    public function __construct(string $parameterName, string $operator = null, int $value = null)
    {
        parent::__construct($parameterName);

        if ($operator !== null && !in_array($operator, $this->validOperators)) {
            throw new InvalidArgumentException(
                "operator must be one of " .
                implode(", ", $this->validOperators) .
                    ", was: \"$operator\""
            );
        }
        $this->operator = $operator;
        $this->value    = $value;
    }

    /**
     * @inheritdoc
     */
    protected function validate(Parameter $parameter, ValidationErrors $errors): bool
    {
        $value = (int)$parameter->getValue();

        if ((string)$value !== $parameter->getValue()) {
            $errors[] = new ValidationError(
                $parameter,
                "parameter \"" . $parameter->getName() . "\"'s value \"" . $parameter->getValue() .
                "\" cannot be treated as integer",
                400
            );
            return false;
        }

        if ($this->operator === null || $this->value === null) {
            return true;
        }

        $res = false;
        $msg =  "parameter \"" . $parameter->getName() . "\"'s value \"" . $parameter->getValue() .
            "\" is not " . $this->operator . " " . $this->value;

        switch ($this->operator) {
            case "=":
                $res = $value === $this->value;
                break;
            case "!=":
                $res = $value !== $this->value;
                break;
            case ">=":
                $res = $value >= $this->value;
                break;
            case "<=":
                $res = $value <= $this->value;
                break;
            case ">":
                $res = $value > $this->value;
                break;
            case "<":
                $res = $value < $this->value;
                break;
        }

        if (!$res) {
            $errors[] = new ValidationError($parameter, $msg, 400);
        }

        return $res;
    }
}
