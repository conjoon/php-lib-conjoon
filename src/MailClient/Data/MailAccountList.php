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

namespace Conjoon\MailClient\Data;

use Conjoon\Core\AbstractList;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;


class MailAccountList extends AbstractList implements Arrayable, Jsonable
{

    /**
     * @Override
     */
    public function toArray(): array
    {
        $d = [];
        foreach ($this->data as $account) {
            $d[] = $account->toArray();
        }
        return $d;
    }


    /**
     * @Override
     */
    public function toJson(JsonStrategy $jsonStrategy = null): array
    {
        return $jsonStrategy ? $jsonStrategy->toJson($this) : $this->toArray();
    }


    /**
     * @Override
     */
    public function getEntityType(): string
    {
        return MailAccount::class;
    }
}
