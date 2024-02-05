<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */


declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data;

use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Data\MailAccountList;
use Conjoon\Core\AbstractList;
use Tests\JsonableTestTrait;
use Tests\TestCase;


class MailAccountListTest extends TestCase
{
    use JsonableTestTrait;


    /**
     * Tests constructor
     */
    public function testClass()
    {
        $mailAddressList = new MailAccountList();
        $this->assertInstanceOf(AbstractList::class, $mailAddressList);

        $this->assertSame(MailAccount::class, $mailAddressList->getEntityType());
    }


    /**
     * Tests toArray
     */
    public function testToArray()
    {
        $mailAccountList = $this->createList();

        $this->assertEquals([
            $mailAccountList[0]->toArray(),
            $mailAccountList[1]->toArray()
        ], $mailAccountList->toArray());
    }

    /**
     * tests toJson
     */
    public function testToJson()
    {
        $mailAccountList = $this->createList();
        $this->runToJsonTest($mailAccountList);
    }


    /**
     * @return MailAccountList
     */
    protected function createList()
    {
        $cfg =[
            "id"              => "dev_sys_conjoon_org",
            "name"            => "conjoon developer",
            "from"            => ["name" => "John Smith", "address" => "dev@conjoon.org"],
            "replyTo"         => ["name" => "John Smith", "address" => "dev@conjoon.org"],
            "inbox_type"      => "IMAP",
            "inbox_address"   => "some.address.server",
            "inbox_port"      => 993,
            "inbox_user"      => "user inbox",
            "inbox_password"  => "password",
            "inbox_ssl"       => true,
            "outbox_address"  => "some.outbox.server",
            "outbox_port"     => 993,
            "outbox_user"     => "user",
            "outbox_password" => "password outbox",
            "outbox_secure"   => "ssl",
            "subscriptions"    => ["[Gmail]"]
        ];

        $mailAccountList = new MailAccountList();
        $mailAccountList[] = new MailAccount($cfg);
        $mailAccountList[] = new MailAccount($cfg);

        return $mailAccountList;
    }
}
