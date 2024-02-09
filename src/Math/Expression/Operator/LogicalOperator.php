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

namespace Conjoon\Math\Expression\Operator;

use Illuminate\Support\Facades\Log;

/**
 * Represents logical operators.
 *
 */
enum LogicalOperator: string implements Operator
{
    use OperatorStringableTrait;

    case AND = "&&";

    case OR = "||";

    case NOT = "!";

    public static function fromString(string $value): ?LogicalOperator {

        $value = strtoupper($value);

        return match ($value) {
            "AND" => LogicalOperator::AND,
            "OR" => LogicalOperator::OR,
            "NOT" => LogicalOperator::NOT,
            default => LogicalOperator::tryFrom($value)
        };
    }
}
