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

namespace Conjoon\Illuminate\Auth;

use Conjoon\Mail\Client\Data\MailAccount as LegacyMailAccount;
use Conjoon\Mail\Client\Data\MailAccountList as LegacyMailAccountList;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Data\MailAccountList;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class ImapUser encapsulates a user for the php-lib-conjoon package, containing
 * associated MailAccount-information.
 *
 * An ImapUser is considered to be be associated with ONE ImapAccount. E.g. for the email-address
 * "address@domain.org" must be one ImapServer in the configuration existing that manages THIS email-address.
 *
 * @package Conjoon\Illuminate\Auth\Imap
 */
class ImapUser implements Authenticatable
{
    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var MailAccount|LegacyMailAccount $mailAccount
     */
    private MailAccount|LegacyMailAccount $mailAccount;


    /**
     * ImapUser constructor.
     *
     * @param string $username
     * @param string $password
     * @param MailAccount|LegacyMailAccount $mailAccount
     */
    public function __construct(string $username, string $password, MailAccount|LegacyMailAccount $mailAccount)
    {
        $this->username = $username;
        $this->password = $password;

        $this->mailAccount = $mailAccount;
    }

    /**
     * Returns the mail account that is connected with the ImapUser's username, whereas
     * the username is considered to be the email-adress. The mail-account managing this email-address
     * will be returned.
     */

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * Returns the MailAccount that belongs to this user with the specified
     * $mailAccountId.
     *
     * Returns null if no MailAccount with the specified id was found.
     *
     * @param string $mailAccountId
     *
     * @return MailAccount|LegacyMailAccount|null
     */
    public function getMailAccount(string $mailAccountId): null|MailAccount|LegacyMailAccount
    {

        if ($this->mailAccount->getId() !== $mailAccountId) {
            return null;
        }
        return $this->mailAccount;
    }

    /**
     * Returns MailAccountLists containing the MailAccounts of this user.
     *
     * @return MailAccountList|LegacyMailAccountList
     */
    public function getMailAccounts(): MailAccountList|LegacyMailAccountList
    {
        $mailAccount = $this->mailAccount;
        if ($mailAccount instanceof LegacyMailAccount) {
            $list = new LegacyMailAccountList();
            $list[] = $mailAccount;
            return $list;
        }
        return MailAccountList::make($mailAccount);
    }


    /**
     * Returns the mail account configured for the specified userId (e.g. the email-address, or the user name),
     * or null if not available.
     *
     * @param string $userId
     *
     * @return MailAccount
     */
    public function getMailAccountForUserId(string $userId): null|MailAccount|LegacyMailAccount
    {
        if (strtolower($this->mailAccount->getInboxUser()) !== strtolower($userId)) {
            return null;
        }

        return $this->mailAccount;
    }


    /**
     * Returns an array representation of this user.
     *
     * @return array
     */
    public function toArray(): array
    {

        return [
            'username' => $this->username,
            'password' => $this->password
        ];
    }


// ------ Authenticable

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string|null
     */
    public function getAuthIdentifierName(): ?string
    {
        return null;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return void
     */
    public function getAuthIdentifier(): ?string
    {
        return null;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): ?string
    {
        return null;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken(): ?string
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
