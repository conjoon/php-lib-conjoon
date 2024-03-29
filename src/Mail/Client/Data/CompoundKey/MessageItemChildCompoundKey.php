<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Conjoon\Mail\Client\Data\CompoundKey;

use Conjoon\Util\JsonStrategy;
use InvalidArgumentException;

/**
 * Class MessageItemChildCompoundKey models a class for compound keys for entities belonging
 * to Messages, such as attachments.
 *
 * @package Conjoon\Mail\Client\Data\CompoundKey
 */
abstract class MessageItemChildCompoundKey extends MessageKey
{
    /**
     * @var string
     */
    protected string $parentMessageItemId;


    /**
     * MessageItemChildCompoundKey constructor.
     *
     * @param string|MessageKey $mailAccountId
     * @param string $mailFolderId
     * @param string|null $parentMessageItemId
     * @param string|null $id
     *
     */
    public function __construct(
        $mailAccountId,
        string $mailFolderId,
        string $parentMessageItemId = null,
        string $id = null
    ) {

        if ($mailAccountId instanceof MessageKey) {
            $id = $mailFolderId;
            $parentMessageItemId = $mailAccountId->getId();
            $mailFolderId = $mailAccountId->getMailFolderId();
            $mailAccountId = $mailAccountId->getMailAccountId();
        } elseif ($id === null || $parentMessageItemId === null) {
            throw new InvalidArgumentException("\"id\" and \"parentMessageItemId\" must not be null.");
        }

        parent::__construct($mailAccountId, $mailFolderId, $id);

        $this->parentMessageItemId = $parentMessageItemId;
    }


    /**
     * @return string
     */
    public function getParentMessageItemId(): string
    {
        return $this->parentMessageItemId;
    }


    /**
     * Returns the MessageKey this MessageItemChildCompoundKey represents.
     * No references between *this* and the returned key exist.
     *
     * @return MessageKey
     */
    public function getMessageKey(): MessageKey
    {
        return new MessageKey(
            $this->getMailAccountId(),
            $this->getMailFolderId(),
            $this->getParentMessageItemId()
        );
    }


    /**
     * Returns true if, and only if all contained values of *this* key are the same
     * value and type compared with the values of $key.
     *
     * @param MessageItemChildCompoundKey $key
     *
     * @return bool
     */
    public function equals(MessageItemChildCompoundKey $key): bool
    {
        return
            $this->getMailAccountId() === $key->getMailAccountId() &&
            $this->getMailFolderId() === $key->getMailFolderId() &&
            $this->getParentMessageItemId() === $key->getParentMessageItemId() &&
            $this->getId() === $key->getId();
    }


    /**
     * Returns an array representation of this object.
     *
     * @return array
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        $json = parent::toJson();
        $json["parentMessageItemId"] = $this->getParentMessageItemId();

        return $json;
    }
}
