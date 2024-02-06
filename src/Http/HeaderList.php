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

namespace Conjoon\Http;

use Conjoon\Data\ParameterList as DataParameterList;

/**
 * Class ParameterList organizes a list of PathParameters.
 *
 * @extends HeaderList<Header>
 */
class HeaderList extends DataParameterList
{
    /**
     * @Override
     */
    public function getEntityType(): string
    {
        return Header::class;
    }
}
