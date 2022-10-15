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

namespace Conjoon\MailClient\Message;

use Conjoon\MailClient\Data\CompoundKey\MessageKey;

/**
 * Class MessageItem models envelope information of a Message.
 *
 *
 * @package Conjoon\MailClient\Message
 * @method getHasAttachments()
 * @method getSize()
 */
class MessageItem extends AbstractMessageItem
{
    /**
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * @var bool|null
     */
    protected ?bool $hasAttachments = null;


    /**
     * MessageItem constructor.
     *
     * @param MessageKey $messageKey
     * @param array|null $data
     *
     * @see configure
     */
    public function __construct(MessageKey $messageKey, array $data = null)
    {

        $this->messageKey = $messageKey;

        if (!$data) {
            return;
        }

        $this->configure($data);
    }


    /**
     * @inheritdoc
     */
    protected function checkType(string $property, $value)
    {

        switch ($property) {
            case "size":
                if (!is_int($value)) {
                    return "int";
                }
                break;

            case "hasAttachments":
                if (!is_bool($value)) {
                    return "bool";
                }
                break;
        }

        return true;
    }



// --------------------------------
//  Arrayable interface
// --------------------------------

    /**
     * Returns an array representing this MessageItem.
     * Only the data will be returned where the values are not null.
     * @return array
     */
    public function toArray(): array
    {
        $data = array_merge([
            "type" => "MessageItem",
            "hasAttachments" => $this->getHasAttachments(),
            "size" => $this->getSize()
        ], parent::toArray());

        return $this->buildArray($data);
    }
}
