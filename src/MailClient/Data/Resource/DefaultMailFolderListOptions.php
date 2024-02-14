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

namespace Conjoon\MailClient\Data\Resource;


class DefaultMailFolderListOptions extends MailFolderListOptions
{
    public function __construct(private readonly array $dissolveNamespaces) {
    }

    public function getDissolveNamespaces(): array {
        return $this->dissolveNamespaces;
    }

}
