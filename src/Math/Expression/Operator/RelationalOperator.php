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

namespace Conjoon\Math\Expression\Operator;

/**
 * Represents relational operators.
 */
enum RelationalOperator: string implements Operator
{
    use OperatorStringableTrait;

    case EQ = "==";

    case NE = "!=";

    case GT = ">";

    case LT = "<";

    case LE = "<=";

    case GE = ">=";

    public static function fromString(string $value): ?RelationalOperator {

        $value = strtoupper($value);

        return match ($value) {
            "=" => RelationalOperator::EQ,
            default => RelationalOperator::tryFrom($value)
        };
    }
}
