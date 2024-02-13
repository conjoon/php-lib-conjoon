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

namespace Conjoon\MailClient\Data;

use BadMethodCallException;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;

/**
 * Class MailAccount models account information for a mail server.
 *
 * @example
 *
 *    $account = new MailAccount([
 *        "id"              => "dev_sys_conjoon_org",
 *        "name"            => "conjoon developer",
 *        "from"            => ["name" => "John Smith", "address" => "dev@conjoon.org"],
 *        "replyTo"         => ["name" => "John Smith", "address" => "dev@conjoon.org"],
 *        "inbox_type"      => "IMAP",
 *        "inbox_address"   => "sfsffs.ffssf.sffs",
 *        "inbox_port"      => 993,
 *        "inbox_user"      => "inboxuser",
 *        "inbox_password"  => "inboxpassword",
 *        "inbox_ssl"       => true,
 *        "outbox_address"  => "sfsffs.ffssf.sffs",
 *        "outbox_port"     => 993,
 *        "outbox_user"     => "outboxuser",
 *        "outbox_password" => "outboxpassword',
 *        'outbox_secure'   => "tls",
 *        'subscriptions'   => ["INBOX"]
 *    ]);
 *
 *    $account->getOutboxSecure(); // true
 *    $account->getInboxPort(); // 993
 *    $account->getReplyTo();   // ['name' => 'John Smith', 'address' => 'dev@conjoon.org'],
 *
 * The property "subscriptions" allows for specifying mailbox the account is subscribed to.
 *
 *
 * @method string getName()
 * @method array getFrom()
 * @method array getReplyTo()
 * @method string getInboxUser()
 * @method string getInboxPassword()
 * @method string getOutboxUser()
 * @method string getOutboxPassword()
 * @method string getId()
 * @method string getInboxAddress()
 * @method string getInboxType()
 * @method int getInboxPort()
 * @method bool getInboxSsl()
 * @method string getOutboxAddress()
 * @method int getOutboxPort()
 * @method string getOutboxSecure()
 * @method array getSubscriptions()
 * @method array getDissolveNamespaces()
 *
 * @noinspection SpellCheckingInspection
 */
class MailAccount implements Jsonable, Arrayable
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string clear text name
     */
    protected string $name;

    /**
     * @var array name, address
     */
    protected array $from;

    /**
     * @var array name, address
     */
    protected array $replyTo;

    /**
     * @var string
     */
    protected string $inbox_type = 'IMAP';

    /**
     * @var string
     */
    protected string $inbox_address;

    /**
     * @var int
     */
    protected int $inbox_port;

    /**
     * @var string
     */
    protected string $inbox_user;

    /**
     * @var string
     */
    protected string $inbox_password;

    /**
     * @var boolean
     */
    protected bool $inbox_ssl;

    /**
     * @var string
     */
    protected string $outbox_address;

    /**
     * @var int
     */
    protected int $outbox_port;

    /**
     * @var string
     */
    protected string $outbox_user;

    /**
     * @var string
     */
    protected string $outbox_password;

    /**
     * @var string
     */
    protected string $outbox_secure;

    /**
     * @var array
     */
    protected array $subscriptions = ["INBOX"];

    /**
     * @var array
     */
    protected array $dissolveNamespaces = ["INBOX", "[GMAIL]", "[Google Mail]"];

    /**
     * MailAccount constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }


    /**
     * Makes sure defined properties in this class are accessible via getter method calls.
     * If needed, camelized methods are resolved to their underscored representations in this
     * class.
     *
     * @param String $method
     * @param Mixed $arguments
     *
     * @return mixed
     *
     * @throws BadMethodCallException if a method is called for which no property exists
     */
    public function __call(string $method, $arguments)
    {
        if (strpos($method, 'get') === 0) {
            if (!in_array($method, ['getReplyTo', 'getDissolveNamespaces'])) {
                $method = substr($method, 3);
                $property = strtolower(preg_replace('/([a-z])([A-Z])/', "$1_$2", $method));
            } else {
                $property = match ($method) {
                    'getReplyTo' => 'replyTo',
                    'getDissolveNamespaces' => 'dissolveNamespaces'
                };
            }

            if (property_exists($this, $property)) {
                return $this->{$property};
            }
        }


        throw new BadMethodCallException("no method named \"$method\" found.");
    }


    /**
     * Returns an Array representation of this instance.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "type" => "MailAccount",
            "name" => $this->getName(),
            "from" => $this->getFrom(),
            "replyTo" => $this->getReplyTo(),
            "inbox_type" => $this->getInboxType(),
            "inbox_address" => $this->getInboxAddress(),
            "inbox_port" => $this->getInboxPort(),
            "inbox_user" => $this->getInboxUser(),
            "inbox_password" => $this->getInboxPassword(),
            "inbox_ssl" => $this->getInboxSsl(),
            "outbox_address" => $this->getOutboxAddress(),
            "outbox_port" => $this->getOutboxPort(),
            "outbox_user" => $this->getOutboxUser(),
            "outbox_password" => $this->getOutboxPassword(),
            "outbox_secure" => $this->getOutboxSecure(),
            "subscriptions" => $this->getSubscriptions(),
            "dissolveNamespaces" => $this->getDissolveNamespaces()
        ];
    }

    /**
     * @inheritdoc
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        return $strategy ? $strategy->toJson($this) : $this->toArray();
    }
}
