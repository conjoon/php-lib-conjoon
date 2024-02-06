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

namespace Tests\Conjoon\MailClient\Data\Resource;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\Data\Resource\MessageBodyDescription;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Tests\TestCase;

/**
 * Tests MessageItem.
 */
class MessageItemDescriptionTest extends TestCase
{
    /**
     * test class
     */
    public function testClass()
    {
        $inst = new MessageItemDescription();
        $this->assertInstanceOf(ResourceDescription::class, $inst);
    }


    /**
     * @return void
     */
    public function testGetType(): void
    {
        $this->assertSame("MessageItem", $this->createDescription()->getType());
    }


    /**
     * Tests getRelationships()
     */
    public function testGetRelationships(): void
    {
        $list = $this->createDescription()->getRelationships();
        $this->assertSame(2, count($list));

        $this->assertInstanceOf(MailFolderDescription::class, $list[0]);
        $this->assertInstanceOf(MessageBodyDescription::class, $list[1]);

        $this->assertSame(
            ["MessageItem", "MessageItem.MailFolder", "MessageItem.MailFolder.MailAccount", "MessageItem.MessageBody"],
            $this->createDescription()->getAllRelationshipPaths(true)
        );

        $this->assertEqualsCanonicalizing(
            ["MessageItem", "MailFolder", "MessageBody", "MailAccount"],
            $this->createDescription()->getAllRelationshipTypes(true)
        );
    }


    /**
     * Tests getFields()
     */
    public function testGetFields(): void
    {
        $this->assertEqualsCanonicalizing([
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
        ], $this->createDescription()->getFields());
    }


    /**
     * tests getDefaultFields()
     */
    public function testGetDefaultFields(): void
    {
        $this->assertEqualsCanonicalizing([
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
        ], $this->createDescription()->getDefaultFields());
    }


    /**
     * @return MessageItemDescription
     */
    protected function createDescription(): MessageItemDescription
    {
        return new MessageItemDescription();
    }
}
