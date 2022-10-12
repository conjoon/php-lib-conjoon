<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Statement\Expression;

use Conjoon\Statement\OperandList;
use BadMethodCallException;
use Error;

/**
 * Trait providing functionality for using existing enum values as static method calls.
 *
 * @example
 *  $expression = RelationalExpression::EQ(
 *     Value::make(1), Value::make(2)
 *  );
 *  // is the same as
 *  $expression = RelationalExpression::make(
 *     RelationalOperator::EQ,
 *     Value::make(1),
 *     Value::make(2)
 *  );
 *
 *  $expression->toString(); // 1 == 2
 */
trait OperatorCallTrait
{
    /**
     * Returns the fqn of the Operator this expression is using.
     *
     * @return string
     */
    abstract public static function getOperatorClass(): string;


    /**
     * Shorthand for directly using static methods named after the available operators for this
     * expression.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return Expression
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $method, array $arguments): Expression
    {
        $method = strtoupper($method);

        try {
            $operator = constant(self::getOperatorClass() . "::$method");
        } catch (Error $error) {
            throw new BadMethodCallException("{$method} does not exist: " . $error->getMessage());
        }

        return new static(
            $operator,
            OperandList::make(...$arguments)
        );
    }
}
