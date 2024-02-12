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

namespace Conjoon\MailClient\Data\CompoundKey;

use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Data\Identifier;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Stringable;

/**
 * Class MessageKey models base class for compound keys for identifying (IMAP) Messages.
 *
 * Each key subclassing CompoundKey has a mailAccountId that represents the id of a specified
 * MailAccount, as well as an "id" field that represents the unique key with which the entity
 * using the CompoundKey can be identified.
 *
 *
 * @package Conjoon\MailClient\Data\CompoundKey
 */
abstract class CompoundKey extends Identifier implements Stringable
{
    /**
     * @var string
     */
    protected string $id;


    /**
     * @var string
     */
    protected string $mailAccountId;

    public static function new(): static {
        return new static(... func_get_args());
    }

    /**
     * CompoundKey constructor.
     *
     * @param string|MailAccount $mailAccountId
     * @param string $id
     */
    public function __construct($mailAccountId, string $id)
    {
        if ($mailAccountId instanceof MailAccount) {
            $mailAccountId = $mailAccountId->getId();
        }
        $this->mailAccountId = (string)$mailAccountId;
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getMailAccountId(): string
    {
        return $this->mailAccountId;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Returns an array representation of this object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'mailAccountId' => $this->getMailAccountId()
        ];
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $strategy = null): string
    {
        if ($strategy) {
            return $strategy->toString($this);
        }
        return json_encode($this->toArray());
    }
}
