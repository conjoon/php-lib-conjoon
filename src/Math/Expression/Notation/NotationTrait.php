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

use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\Expression\Operator\LogicalOperator;
use Conjoon\Math\Expression\Operator\RelationalOperator;

trait NotationTrait
{

    /**
     * Returns true if the passed argument is a valid operator.
     *
     * @param string $value
     * @return bool
     */
    public function isValidOperator(string $value): bool
    {
        return in_array($value, $this->getRelationalOperators()) ||
            $this->isLogicalOperator($value) ||
            in_array($value, $this->getFunctions());
    }



    /**
     * Returns true if the submitted argument is a logical operator.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isLogicalOperator(string $value): bool
    {
        return in_array($value, $this->getLogicalOperators());
    }


    /**
     * Returns the list of relational operators available.
     *
     * @return string[]
     */
    public function getRelationalOperators(): array
    {
        return array_merge(array_map(fn($enum) => $enum->value, RelationalOperator::cases()), ["="]);
    }


    /**
     * Returns the list of logical operators supported.
     *
     * @return string[]
     */
    public function getLogicalOperators(): array
    {
        return [LogicalOperator::OR->value, "OR", LogicalOperator::AND->value, "AND"];
    }


    /**
     * Returns the list of functions supported.
     *
     * @return string[]
     */
    public function getFunctions(): array
    {
        return array_map(fn($enum) => $enum->value, FunctionalOperator::cases());
    }

}

