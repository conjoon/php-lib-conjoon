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

namespace Conjoon\Core\Contract;

/**
 * Interface JsonStrategy.
 */
interface JsonStrategy
{
    /**
     * Returns a JSON representation of the data passed to this method.
     *
     * @param Arrayable $source
     *
     * @return array
     */
    public function toJson(Arrayable $source): array;
}
