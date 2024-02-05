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

namespace Conjoon\Mail\Client\Data;

use Conjoon\Util\AbstractList;
use Conjoon\Util\Jsonable;
use Conjoon\Util\JsonStrategy;
use Conjoon\Util\Stringable;


class MailAccountList extends AbstractList implements Jsonable
{
    /**
     * @Override
     */
    public function toJson(JsonStrategy $jsonStrategy = null): array
    {
        $d = [];

        foreach ($this->data as $account) {
            $d[] = $account->toJson();
        }


        return $d;
    }

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return MailAccount::class;
    }
}
