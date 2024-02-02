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

/**
 * ResourceDescription for a MailAccount.
 *
 */
class MailAccountDescription extends ResourceDescription
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return "MailAccount";
    }


    /**
     * @return ResourceDescriptionList
     */
    public function getRelationships(): ResourceDescriptionList
    {
        return new ResourceDescriptionList();
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
            "folderType",
            "from",
            "replyTo",
            "inbox_address",
            "inbox_port",
            "inbox_user",
            "inbox_password",
            "inbox_ssl",
            "outbox_address",
            "outbox_port",
            "outbox_user",
            "outbox_password",
            "outbox_secure",
            "subscriptions"
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
            "folderType",
            "from",
            "replyTo",
            "inbox_address",
            "inbox_port",
            "inbox_user",
            "inbox_password",
            "inbox_ssl",
            "outbox_address",
            "outbox_port",
            "outbox_user",
            "outbox_password",
            "outbox_secure",
            "subscriptions"
        ];
    }
}
