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

namespace Tests\Conjoon\MailClient\Data;

use BadMethodCallException;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;
use Tests\TestCase;

/**
 * Class MailAccountTest
 * @package Tests\Conjoon\MailClient\Data
 */
class MailAccountTest extends TestCase
{
    protected array $accountConfig = [
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


    /**
     * @return void
     */
    public function testInstance()
    {
        $account = new MailAccount($this->accountConfig);

        $this->assertInstanceOf(Jsonable::class, $account);
        $this->assertInstanceOf(Arrayable::class, $account);
    }

    /**
     * magic methods
     */
    public function testGetter()
    {
        $config = $this->accountConfig;

        $oldRoot = $config["subscriptions"];
        $this->assertSame(["[Gmail]"], $oldRoot);
        unset($config["subscriptions"]);

        $account = new MailAccount($config);
        $this->assertSame(["INBOX"], $account->getSubscriptions());

        $config["subscriptions"] = $oldRoot;
        $account = new MailAccount($config);

        foreach ($config as $property => $value) {
            if ($property === "from" || $property === "replyTo") {
                $method = $property == "from" ? "getFrom" : "getReplyTo";
            } else {
                $camelKey = "_" . str_replace("_", " ", strtolower($property));
                $camelKey = ltrim(str_replace(" ", "", ucwords($camelKey)), "_");
                $method   = "get" . ucfirst($camelKey);
            }

            $this->assertSame($value, $account->{$method}());
        }
    }


    /**
     * not existing method
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testGetterException()
    {

        $config = $this->accountConfig;
        $account = new MailAccount($config);

        $this->expectException(BadMethodCallException::class);

        $account->getSomeFoo();
    }


    /**
     * toArray()
     */
    public function testToArray()
    {
        $config = $this->accountConfig;
        $config["type"] = "MailAccount";

        $account = new MailAccount($config);

        $this->assertEquals($config, $account->toArray());
    }


    /**
     * toArray()
     */
    public function testToJson()
    {
        $config = $this->accountConfig;
        $config["type"] = "MailAccount";

        $account = new MailAccount($config);

        $this->assertEquals($config, $account->toJson());

        $strategyMock =
            $this->getMockBuilder(JsonStrategy::class)
                ->getMockForAbstractClass();

        $strategyMock
            ->expects($this->exactly(1))
            ->method("toJson")
            ->with($account)
            ->willReturn($account->toArray());

        $this->assertEquals($config, $account->toJson($strategyMock));
    }
}
