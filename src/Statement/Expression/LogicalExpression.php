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

use Conjoon\Statement\InvalidOperandException;
use Conjoon\Statement\Operand;
use Conjoon\Statement\Expression\Operator\LogicalOperator;
use Conjoon\Statement\OperandList;

/**
 * Represents a logical expression.
 *
 * @example
 *
 *  $operator   = LogicalOperator::NOT;
 *  $expression = LogicalExpression::make(
 *     $operator,
 *     new Value(true)
 *  );
 *  $expression->toString(); // !true
 *
 *  $operator   = LogicalOperator::AND;
 *  $expression = LogicalExpression::make(
 *     $operator, Value::make(1), Value::make(2), Value::make(3)
 *  );
 *  $expression->toString(); // 1 && 2 && 3
 */
class LogicalExpression extends Expression
{
    use OperatorCallTrait;

    /**
     * @inheritdoc
     */
    public static function getOperatorClass(): string
    {
        return LogicalOperator::class;
    }


    /**
     * Factory method for easily creating a LogicalExpression.
     *
     * @param LogicalOperator $operator
     *
     * @param Operand ...$operand list of operands for the constructor
     *
     * @return LogicalExpression
     */
    public static function make(LogicalOperator $operator, Operand ...$operand): LogicalExpression
    {
        return new self(
            $operator,
            OperandList::make(...$operand)
        );
    }


    /**
     * Constructor.
     * A logical expression expects an arbitrary number of operands for a conjunction (AND)
     * or disjunction (OR), and exactly one operand for a negation (!, i.e. NOT).
     *
     * @param OperandList $operands
     *
     * @param LogicalOperator $operator
     */
    public function __construct(LogicalOperator $operator, OperandList $operands)
    {
        $operandCount = count($operands);

        if ($operator === LogicalOperator::NOT && $operandCount !== 1) {
            throw new InvalidOperandException(
                "LogicalExpression representing a negation expects 1 operand, {$operandCount} given"
            );
        }

        if ($operandCount <= 1 && $operator !== LogicalOperator::NOT) {
            throw new InvalidOperandException(
                "LogicalExpression representing a conjunction or disjunction expects at least 2 " .
                "operands, {$operandCount} given"
            );
        }

        $this->operands = $operands;
        $this->operator = $operator;
    }
}
