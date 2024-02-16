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
 * ResourceDescription for a MessageItem.
 *
 */
class MessageItemDescription extends ResourceDescription
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return "MessageItem";
    }


    /**
     * @return ResourceDescriptionList
     */
    public function getRelationships(): ResourceDescriptionList
    {
        $list = new ResourceDescriptionList();
        $list[] = MailFolderDescription::getInstance();
        $list[] = MessageBodyDescription::getInstance();

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
            "from",
            "to",
            "subject",
            "date",
            "seen",
            "answered",
            "draft",
            "flagged",
            "recent",
            "charset",
            "references",
            "messageId",
            "size",
            "hasAttachments",
            "cc",
            "bcc",
            "replyTo",
            "draftInfo"
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
            "from",
            "to",
            "subject",
            "date",
            "seen",
            "answered",
            "draft",
            "flagged",
            "recent",
            "charset",
            "references",
            "messageId",
            "size",
            "hasAttachments"
        ];
    }
}
