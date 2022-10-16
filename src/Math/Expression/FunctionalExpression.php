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

namespace Conjoon\Math\Expression;

use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Operand;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\OperandList;
use Psr\Log\InvalidArgumentException;

/**
 * Represents a functional expression.
 *
 * FunctionalExpression allows for using the FunctionalOperator::IN:
 * The FunctionalOperator::IN  has two operands: The left-hand operand is a single Operand,
 * the right-hand operand is an OperandList. The expression evaluates to true if the left-hand
 * Operand is a member of the right-hand OperandList.
 *
 * @example
 *
 *  $operator   = FunctionalOperator::IN;
 *  $expression = new FunctionalExpression(
 *     $operator,
 *     OperandList::make(
 *         new VariableName("seen"),
 *         Value::make(1),
 *         Value::make("foo"),
 *         Value::make(false),
 *         Value::make(true)
 *     )
 *  );
 *  $expression->toString(); // seen IN (1, "foo", false, true)
 *
 */
class FunctionalExpression extends Expression
{
    use OperatorCallTrait;

    /**
     * @inheritdoc
     */
    public static function getOperatorClass(): string
    {
        return FunctionalOperator::class;
    }


    /**
     * Constructor.
     *
     * @param OperandList $operands
     *
     * @param FunctionalOperator $operator
     *
     * @throws InvalidOperandException
     */
    public function __construct(FunctionalOperator $operator, OperandList $operands)
    {
        $operandCount = count($operands);

        if ($operator === FunctionalOperator::IN && $operandCount < 2) {
            throw new InvalidOperandException(
                "FunctionalExpression \"IN\" expects at least 2 operands, {$operandCount} given"
            );
        }

        $this->operands = $operands;
        $this->operator = $operator;
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $strategy = null): string
    {
        if ($strategy) {
            return parent::toString($strategy);
        }

        if ($this->getOperator() === FunctionalOperator::IN) {
            return "(" . implode(" ", [
                $this->getOperands()[0]->toString(),
                $this->getOperator()->toString(),
                "(" . implode(", ", array_slice($this->getOperands()->toArray(), 1)) . ")",
            ]) . ")";
        }

        return parent::toString();
    }
}
