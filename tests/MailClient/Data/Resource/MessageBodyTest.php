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


class MessageBodyTest extends TestCase
{
    public function testClass()
    {
        $inst = new MessageBodyDescription();
        $this->assertInstanceOf(ResourceDescription::class, $inst);
    }


    public function testGetType(): void
    {
        $this->assertSame("MessageBody", $this->createDescription()->getType());
    }


    public function testGetRelationships(): void
    {
        $list = $this->createDescription()->getRelationships();
        $this->assertSame(2, count($list));

        $this->assertInstanceOf(MailFolderDescription::class, $list[0]);
        $this->assertInstanceOf(MessageItemDescription::class, $list[1]);

        $this->assertSame(
            ["MessageBody", "MessageBody.MailFolder", "MessageBody.MailFolder.MailAccount", "MessageBody.MessageItem"],
            $this->createDescription()->getAllRelationshipPaths(true)
        );

        $this->assertEqualsCanonicalizing(
            ["MessageItem", "MailFolder", "MessageBody", "MailAccount"],
            $this->createDescription()->getAllRelationshipTypes(true)
        );
    }


    public function testGetFields(): void
    {
        $this->assertEqualsCanonicalizing([
            "textPlain",
            "textHtml"
        ], $this->createDescription()->getFields());
    }


    public function testGetDefaultFields(): void
    {
        $this->assertEquals([
            "textPlain",
            "textHtml"
        ], $this->createDescription()->getDefaultFields());
    }


    protected function createDescription(): MessageBodyDescription
    {
        return new MessageBodyDescription();
    }
}
