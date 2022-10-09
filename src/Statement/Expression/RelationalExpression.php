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
use Conjoon\Statement\Expression\Operator\RelationalOperator;
use Conjoon\Statement\OperandList;

/**
 * Represents a relational expression.
 * Relational expressions evaluate to a boolean value.
 *
 * @example
 *
 *   $operator = RelationalOperator::IS;
 *   $expression = RelationalExpression::make(
 *       $operator,
 *       new Value(1),
 *       new Value(2)
 *   );
 *
 *  $expression->toString(); // 1 == 2
 */
class RelationalExpression extends Expression
{
    /**
     * Factory method for easily creating a RelationalExpression.
     *
     * @param RelationalOperator $operator
     * @param Operand $lftOperand
     * @param Operand $rtOperand
     *
     * @return RelationalExpression
     */
    public static function make(RelationalOperator $operator, Operand $lftOperand, Operand $rtOperand)
    {
        return new self(
            $operator,
            OperandList::make($lftOperand, $rtOperand)
        );
    }

    /**
     * Constructor.
     * A relational expression expects two operands.
     *
     * @param OperandList $operands
     *
     * @param RelationalOperator $operator
     */
    public function __construct(RelationalOperator $operator, OperandList $operands)
    {
        if (count($operands) !== 2) {
            throw new InvalidOperandException(
                "RelationalExpression expects 2 operands, " . count($operands) . " given"
            );
        }

        $this->operands = $operands;
        $this->operator = $operator;
    }
}
