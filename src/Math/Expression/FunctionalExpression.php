<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\Math\Expression;

use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\OperandList;

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
