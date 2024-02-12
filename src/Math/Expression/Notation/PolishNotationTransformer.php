<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\Math\Expression\Notation;

use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\FunctionalExpression;
use Conjoon\Math\Expression\LogicalExpression;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\Expression\Operator\LogicalOperator;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Math\Expression\Operator\RelationalOperator;
use Conjoon\Math\Expression\RelationalExpression;
use Conjoon\Math\OperandList;
use Conjoon\Math\Value;
use Conjoon\Math\VariableName;


class PolishNotationTransformer
{
    use NotationTrait;

    /**
     * Transforms the specified array containing an expression in polish notation
     * to an Expression.
     *
     * @param array $input
     * @return Expression
     */
    public function transform(array $input ): Expression
    {

        $parse = function ($value) use (&$parse) {

            if (!is_array($value)) {
                return $value;
            }

            $list = new OperandList();
            foreach ($value as $operator => $filter) {

                $expressionClass = $this->getExpressionClass($operator);
                if ($expressionClass != null) {
                    $res = $parse($filter);
                    return new $expressionClass(
                        $this->toOperator($operator),
                        ($res instanceof OperandList) ? $res : OperandList::make($res)
                    );
                } else if (is_string($operator)) {

                    $ol = new OperandList();
                    $ol[] = VariableName::make($operator);
                    $ol[] = Value::make($filter);
                    return $ol;
                }

                $list[] = $parse($filter);
            }

            return $list;
        };

        return $parse($input);

    }

    private function toOperator(string $operator): ?Operator {

        $operator = strtoupper($operator);

        if ($this->isLogicalOperator($operator)) {
            return LogicalOperator::fromString($operator);
        }

        if ($this->isRelationalOperator($operator)) {
            return RelationalOperator::fromString($operator);
        }

        if ($this->isFunctionalOperator($operator)) {
            return FunctionalOperator::tryFrom($operator);
        }

        return null;

    }

    private function getExpressionClass(mixed $operator): ?string {

        if (!is_string($operator)) {
            return null;
        }

        $operator = strtoupper($operator);

        return match (true) {
            $this->isLogicalOperator($operator) => LogicalExpression::class,
            $this->isRelationalOperator($operator) => RelationalExpression::class,
            $this->isFunctionalOperator($operator) => FunctionalExpression::class,
            default => null,
        };

    }

}
