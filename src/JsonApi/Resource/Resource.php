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

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;

/**
 * Interface for Resources that serve as conceptually representatives for discoverable entities.
 */
class Resource implements Jsonable
{
    private Arrayable&Jsonable $target;

    public function __construct(Arrayable&Jsonable $target) {
        $this->target = $target;
    }

    public function toJson(JsonStrategy $strategy = null): array
    {
        return $strategy ? $strategy->toJson($this->target) : $this->target->toArray();
    }

}
