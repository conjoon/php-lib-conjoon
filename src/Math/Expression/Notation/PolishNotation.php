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

namespace Conjoon\Math\Expression\Notation;

use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Math\Operand;
use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Math\Value;
use Conjoon\Math\Variable;

/**
 * StringStrategy for transforming Expressions to polish notation.
 *
 * @example
 *  $operator = RelationalOperator::EQ;
 *  $expression = RelationalExpression::make(
 *            $operator,
 *            new Value(1),
 *           new Value(2)
 *  );
 *
 *   $this->assertSame("== 1 2", $expression->toString(new PolishNotation()));
 *
 *
 */
class PolishNotation implements StringStrategy
{
    /**
     * Transforms the $target into polish notation.
     *
     * @param Stringable $target
     *
     * @return string
     *
     * @throws UnexpectedTypeException if $target is not an Expression.
     */
    public function toString(Stringable $target): string
    {
        if ($target instanceof Variable) {
            return $target->getName()->toString() . " " . $target->getValue()->toString($this);
        }

        if ($target instanceof Value) {
            return is_array($target->value()) ?  "(" . implode(", ", $target->value()) . ")" : $target->toString();
        }

        if ($target instanceof Expression) {
            $ops = $target->getOperands();

            return
                implode(
                    " ",
                    array_merge(
                        [$target->getOperator()->toString($this)],
                        $ops->map(fn ($op) => $op->toString($this))
                    )
                );
        }

        if ($target instanceof Operator) {
            return $target->toString();
        }

        if ($target instanceof Operand) {
            return $target->toString();
        }

        throw new UnexpectedTypeException(
            sprintf(
                "Expected an argument of type %1s, %2s or %3s",
                Expression::class,
                Operator::class,
                Operand::class
            )
        );
    }
}
