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

namespace Conjoon\Math;

use Conjoon\Core\AbstractList;

/**
 * Represents a list of Operands.
 */
class OperandList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return Operand::class;
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $res = [];

        foreach ($this->data as $data) {
            $res[] = $data->toString();
        }

        return $res;
    }
}
