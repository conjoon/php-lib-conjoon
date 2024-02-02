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

namespace Conjoon\Net\Uri\Component\Query;

use Conjoon\Data\ParameterList as DataParameterList;

/**
 * Class ParameterList organizes a list of QueryParameters.
 *
 * @extends ParameterList<Parameter>
 */
class ParameterList extends DataParameterList
{
    /**
     * @Override
     */
    public function getEntityType(): string
    {
        return Parameter::class;
    }
}
