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
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Tests\TestCase;

/**
 * Tests MailAccount.
 */
class MailAccountDescriptionTest extends TestCase
{
    protected $defaultFields = [
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


    /**
     * test class
     */
    public function testClass()
    {
        $inst = new MailAccountDescription();
        $this->assertInstanceOf(ResourceDescription::class, $inst);
    }


    /**
     * @return string
     */
    public function testGetType(): void
    {
        $this->assertSame("MailAccount", $this->createDescription()->getType());
    }


    /**
     * Tests getRelationships()
     */
    public function testGetRelationships(): void
    {
        $list = $this->createDescription()->getRelationships();
        $this->assertSame(0, count($list));

        $this->assertSame(
            ["MailAccount"],
            $this->createDescription()->getAllRelationshipPaths(true)
        );

        $this->assertEqualsCanonicalizing(
            ["MailAccount"],
            $this->createDescription()->getAllRelationshipTypes(true)
        );
    }


    /**
     * Tests getFields()
     */
    public function testGetFields(): void
    {
        $this->assertEquals(
            $this->defaultFields,
            $this->createDescription()->getFields()
        );
    }


    /**
     * tests getDefaultFields()
     */
    public function testGetDefaultFields(): void
    {
        $this->assertEquals(
            $this->defaultFields,
            $this->createDescription()->getDefaultFields()
        );
    }


    /**
     * @return MailAccountDescription
     */
    protected function createDescription(): MailAccountDescription
    {
        return new MailAccountDescription();
    }
}
