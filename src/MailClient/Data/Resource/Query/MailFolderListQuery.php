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

namespace Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\Resource\RepositoryQuery;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;


abstract class MailFolderListQuery extends RepositoryQuery
{
    /**
     * Returns the fields that should be queried. If no fields where specified, this implementation
     * will return the default fields of the resource target for this query.
     *
     * @return array
     */
    abstract public function getFields(): array;


    function getResourceDescription(): MailFolderDescription
    {
        return new MailFolderDescription();
    }
}
