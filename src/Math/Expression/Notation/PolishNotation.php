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

namespace Conjoon\Math\Expression\Notation;

use Conjoon\Core\Data\StringStrategy;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Math\Operand;
use Conjoon\Core\Exception\UnexpectedTypeException;

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
     * @param mixed $target
     *
     * @return string
     *
     * @throws UnexpectedTypeException if $target is not an Expression.
     */
    public function toString(Stringable $target): string
    {
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
