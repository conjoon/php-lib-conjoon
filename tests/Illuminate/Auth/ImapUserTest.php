<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Illuminate\Auth;

use Conjoon\Mail\Client\Data\MailAccount;
use Conjoon\Illuminate\Auth\ImapUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

/**
 * Class ImapUserTest
 * @package Tests\Conjoon\Illuminate\Auth\Imap
 */
class ImapUserTest extends TestCase
{
    /**
     * Tests ImapUser getter/constructor.
     */
    public function testClass()
    {
        $mailAccount = new MailAccount(["id" => "foo"]);
        $username    = "foo";
        $password     = "bar";

        $user = $this->createUser($username, $password, $mailAccount);
        $this->assertInstanceOf(Authenticatable::class, $user);

        $this->assertSame($username, $user->getUsername());
        $this->assertSame($password, $user->getPassword());
        $this->assertSame([
            "username" => $username,
            "password" => $password
        ], $user->toArray());

        $this->assertSame($mailAccount, $user->getMailAccount($mailAccount->getId()));

        $accounts = $user->getMailAccounts();

        foreach ($accounts as $account) {
            $this->assertSame($account, $user->getMailAccount($account->getId()));
        }
    }


    /**
     * Authenticable interface
     */
    public function testAuthenticable()
    {
        $user = $this->createUser();
        $user->setRememberToken("token");
        $this->assertNull($user->getAuthIdentifierName());
        $this->assertNull($user->getAuthIdentifier());
        $this->assertNull($user->getAuthPassword());
        $this->assertNull($user->getRememberToken());
        $this->assertNull($user->getRememberTokenName());
    }


    /**
     * Tests getMailAccountForEmailAddress
     * @return void
     */
    public function testGetMailAccountForEmailAddress()
    {
        $mailAccount = new MailAccount(["id" => "foo", "inbox_user" => "user", "inbox_password" => "password"]);

        $user = $this->createUser(
            $mailAccount->getInboxUser(),
            $mailAccount->getInboxPassword(),
            $mailAccount
        );

        $this->assertNull($user->getMailAccountForUserId("random"));

        $this->assertSame($mailAccount, $user->getMailAccountForUserId($mailAccount->getInboxUser()));
    }


    /**
     * Creates a new user.
     *
     * @param string|null $username
     * @param string|null $password
     * @param MailAccount|null $mailAccount
     *
     * @return ImapUser
     */
    protected function createUser(
        string $username = null,
        string $password = null,
        MailAccount $mailAccount = null
    ): ImapUser {
        $mailAccount = $mailAccount ?? new MailAccount(["id" => "foo"]);
        $username    = $username ?? "foobar";
        $password    = $password ?? "barfoo";

        return new ImapUser($username, $password, $mailAccount);
    }
}
