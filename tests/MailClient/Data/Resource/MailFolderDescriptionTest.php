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

namespace Tests\App\Http\V0\JsonApi\Resource;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Tests\TestCase;

/**
 * Tests MailFolder.
 */
class MailFolderDescriptionTest extends TestCase
{

    public function testClass()
    {
        $inst = $this->createDescription();
        $this->assertInstanceOf(ResourceDescription::class, $inst);
    }


    public function testGetType(): void
    {
        $this->assertSame("MailFolder", $this->createDescription()->getType());
    }


    public function testGetRelationships(): void
    {
        $list = $this->createDescription()->getRelationships();
        $this->assertSame(1, count($list));

        $this->assertInstanceOf(MailAccountDescription::class, $list[0]);

        $this->assertSame(
            ["MailFolder", "MailFolder.MailAccount"],
            $this->createDescription()->getAllRelationshipPaths(true)
        );

        $this->assertEqualsCanonicalizing(
            ["MailFolder", "MailAccount"],
            $this->createDescription()->getAllRelationshipTypes(true)
        );
    }


    public function testGetFields(): void
    {
        $this->assertEquals([
            "name",
            "data",
            "folderType",
            "unreadMessages",
            "totalMessages"
        ], $this->createDescription()->getFields());
    }


    public function testGetDefaultFields(): void
    {
        $this->assertEquals([
            "name",
            "data",
            "folderType",
            "unreadMessages",
            "totalMessages"
        ], $this->createDescription()->getDefaultFields());
    }


    private function createDescription(): MailFolderDescription
    {
        return new MailFolderDescription();
    }
}
