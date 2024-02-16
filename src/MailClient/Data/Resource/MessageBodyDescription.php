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


class MessageBodyDescription extends ResourceDescription
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return "MessageBody";
    }


    /**
     * @return ResourceDescriptionList
     */
    public function getRelationships(): ResourceDescriptionList
    {
        $list = new ResourceDescriptionList();
        $list[] = MailFolderDescription::getInstance();
        $list[] = MessageItemDescription::getInstance();

        // MessageBody.MailFolder
        // MessageBody.MessageItem
        // MessageBody.MailFolder.MailAccount

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
            "textHtml",
            "textPlain"
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
            "textPlain",
            "textHtml"
        ];
    }
}
