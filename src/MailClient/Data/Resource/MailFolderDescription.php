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

namespace Conjoon\MailClient\Data\Resource;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Net\Uri\Component\Path\Template;

/**
 * ResourceDescription for a MailFolder.
 *
 */
class MailFolderDescription extends ResourceDescription
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return "MailFolder";
    }


    /**
     * @return ResourceDescriptionList
     */
    public function getRelationships(): ResourceDescriptionList
    {
        $list = new ResourceDescriptionList();
        $list[] = new MailAccountDescription();

        return $list;
    }


    /**
     * Returns all fields the entity exposes.
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return [
            "name",
            "data",
            "folderType",
            "unreadMessages",
            "totalMessages"
        ];
    }


    /**
     * Default fields to pass to the lower level api.
     *
     * @return array
     */
    public function getDefaultFields(): array
    {
        return [
            "name",
            "data",
            "folderType",
            "unreadMessages",
            "totalMessages"
        ];
    }
}
