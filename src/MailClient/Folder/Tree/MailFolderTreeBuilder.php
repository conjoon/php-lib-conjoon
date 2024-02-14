<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Folder\Tree;

use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\Folder\MailFolderList;

/**
 * Interface MailFolderTreeBuilder.
 *
 *
 * @package Conjoon\MailClient\Folder\Tree
 */
interface MailFolderTreeBuilder
{
    /**
     * Iterates through the list of ListMailFolders in $mailFolderList and returns
     * a tree structure representing this list. The tree structure does not necessarily start
     * with a root folder and allows for returning a MailFolderChildList which can either hold
     * one entry (representing the root) or multiple entries.
     * The $query may provide information about which fields to return with the MailFolders.
     *
     * @param MailFolderList $mailFolderList
     * @param MailFolderListQuery $query
     *
     * @return MailFolderChildList
     */
    public function listToTree(
        MailFolderList $mailFolderList,
        MailFolderListQuery $query
    ): MailFolderChildList;
}
