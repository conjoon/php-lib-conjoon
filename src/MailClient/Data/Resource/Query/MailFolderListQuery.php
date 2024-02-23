<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2021-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\Filter\Filter;
use Conjoon\Data\Resource\RepositoryQuery;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\Data\Resource\MailFolderListOptions;


abstract class MailFolderListQuery extends RepositoryQuery
{
    /**
     * Returns the fields that should be queried. If no fields where specified, this implementation
     * will return the default fields of the resource target for this query.
     *
     * @param string|null $className
     *
     * @return ?array Returns the array of fields that should be queried, or null if the
     *  specified ResourceDescription was not found in this class ResourceDescriptionList
     */
    abstract public function getFields(string $className = null): ?array;

    /**
     * Returns filter information for this query.
     * Returns null if no filter information is  available.
     *
     * @return Filter|null
     */
    abstract public function getFilter(): ?Filter;


    abstract public function getOptions(): ?MailFolderListOptions;


    final function getResourceDescription(): MailFolderDescription
    {
        return MailFolderDescription::getInstance();
    }
}
