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

namespace Conjoon\JsonApi\Resource;

use Conjoon\Core\AbstractList;
use InvalidArgumentException;
use OutOfBoundsException;
use TypeError;


class ResourceList extends AbstractList
{
    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return Resource::class;
    }
}
