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

namespace Conjoon\Data;

use Conjoon\Core\AbstractList;
use Conjoon\Data\Parameter;

/**
 * Class ParameterList organizes a list of Parameters.
 *
 * @extends AbstractList<Parameter>
 */
class ParameterList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return Parameter::class;
    }


    /**
     * @inheritdoc
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->data as $parameter) {
            $data[$parameter->getName()] = $parameter->getValue();
        }

        return $data;
    }
}
