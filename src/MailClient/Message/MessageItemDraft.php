<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2020-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\MailClient\Message;

use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Exception\MailClientException;

/**
 * Class MessageItemDraft models envelope information of a Message Draft.
 * A MessageDraft that was stored and exists physically provides also a MessageKey.
 *
 * A message draft may actually be in "draft" state, i.e. physically not existing by omitting
 * the MessageKey when creating an instance. Once a MessageDraft is about to get saved, setMessageKey
 * can be used for creating the compound key for the draft.
 *
 * @package Conjoon\MailClient\Message
 * @method getReplyTo()
 * @method getBcc()
 * @method getCc()
 * @method getDraftInfo()
 */
class MessageItemDraft extends AbstractMessageItem
{
    use DraftTrait;


    /**
     * A json encoded array, encoded as a base64-string, containing information about the
     * mailAccountId, the mailFolderId and the messageItemId this draft references,
     * in this order.
     * This value will be set by the client once a draft gets saved that is created
     * for a reply-to/-all regarding a message, and will be reused once the draft
     * gets send to update the message represented by the info in this field with
     * appropriate message flags (e.g. \answered).
     *
     * @var string|null
     */
    protected ?string $draftInfo = null;


    /**
     * Allows for passing only the data for the AbstractMessageItemDraft w/o a MessageKey.
     *
     *
     * @param MessageKey|array|null $messageKey
     * @param array|null $data
     */
    public function __construct($messageKey = null, $data = null)
    {
        $this->draft = true;
        parent::__construct($messageKey, $data);
    }


    /**
     * Sets the "messageKey" by creating a new MessageItemDraft with the specified
     * key and returning a new instance with this data.
     * No references to any data of the original instance will be available.
     * The state of Modifiable will not carry over.
     *
     * @param MessageKey $messageKey
     *
     * @return $this
     */
    public function setMessageKey(MessageKey $messageKey): MessageItemDraft
    {

        $d = $this->toJson();

        $draft = new self($messageKey);

        $draft->suspendModifiable();
        foreach ($d as $key => $value) {
            if (in_array($key, ["id", "mailAccountId", "mailFolderId"])) {
                continue;
            }

            $setter = "set" . ucfirst($key);
            $getter = "get" . ucfirst($key);
            $copyable = $this->{$getter}();

            if ($copyable === null) {
                continue;
            }

            if (in_array($key, ["from", "replyTo", "to", "cc", "bcc"])) {
                if ($copyable) {
                    $draft->{$setter}($copyable->copy());
                }
            } else {
                $draft->{$setter}($this->{$getter}());
            }
        }
        $draft->resumeModifiable();
        return $draft;
    }


    /**
     * Sets the $draftInfo for this MessageItemDraft and throws if
     * the value was already set.
     *
     * @param string|null $draftInfo
     * @return $this
     */
    public function setDraftInfo(string $draftInfo = null): MessageItemDraft
    {

        if (is_string($this->getDraftInfo())) {
            throw new MailClientException("\"draftInfo\" was already set.");
        }

        $this->draftInfo = $draftInfo;

        return $this;
    }


// --------------------------------
//  Arrayable interface
// --------------------------------

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $data = array_merge(parent::toArray(), [
            'cc' => $this->getCc() ? $this->getCc()->toArray() : null,
            'bcc' => $this->getBcc() ? $this->getBcc()->toArray() : null,
            'replyTo' => $this->getReplyTo() ? $this->getReplyTo()->toArray() : null
        ]);

        return $this->buildArray($data);
    }
}
