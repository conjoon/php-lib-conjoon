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

namespace Conjoon\MailClient\Protocol\Imap\Search;

use Conjoon\Core\Strategy\StringStrategy;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Filter\Filter;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Math\Operand;

/**
 * StringStrategy for transforming Expressions to IMAP search queries.
 */
class SearchQueryStrategy implements StringStrategy
{
    /**
     * Transforms the $target into IMAP search query notation.
     *
     * @param mixed $target
     *
     * @return string
     *
     * @throws UnexpectedTypeException if $target is not an Expression.
     */
    public function toString(Stringable $target): string
    {
        if ($target instanceof Filter) {
            $target = $target->getExpression();
        }

        if ($target instanceof Expression) {
            $ops      = $target->getOperands();
            $operator = $target->getOperator();

            if ($ops[0]->toString() === "UID" && $operator->toString() === ">=") {
                return "(UID " . implode(":", array_slice($ops->toArray(), 1)) . ":*)";
            }

            if ($ops[0]->toString() === "UID" && $operator->toString() === "IN") {
                return "(UID " . implode(":", array_slice($ops->toArray(), 1))  . ")";
            }

            if ($operator->toString() === "==" && is_bool($ops[1]->getValue())) {
                return "(" . $ops[0]->toString() . ")";
            }

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
            $repl = ["||" => "OR", "&&" => "AND"];
            $str = $target->toString();
            return $repl[$str] ?? $str;
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
