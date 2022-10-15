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

namespace Conjoon\Mail\Client\Message;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Data\AbstractList;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Strategy\JsonStrategy;

/**
 * Class MessageItemList organizes a list of MessageItems.
 *
 * @example
 *
 *    $list = new MessageItemList();
 *
 *    $item = new MessageItem(new MessageKey("INBOX", "232"), null);
 *    $list[] = $item;
 *
 *    foreach ($list as $key => $mItem) {
 *        // iterating over the item
 *    }
 *
 * @package Conjoon\Mail\Client\Message
 */
class MessageItemList extends AbstractList implements Arrayable, Jsonable
{
// -------------------------
//  AbstractList
// -------------------------

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return MessageItem::class;
    }



// --------------------------------
//  Arrayable interface
// --------------------------------

    /**
     * Returns an array representing this MessageItemList.
     *
     * Each entry in the returning array holds an array representation of
     * a MessageItem.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->data as $messageItem) {
            $data[] = $messageItem->toArray();
        }

        return $data;
    }

// --------------------------------
//  Jsonable interface
// --------------------------------

    /**
     * Returns an array representing this MessageItemList.
     *
     * Each entry in the returning array holds a JSON representation of
     * a MessageItem.
     *
     * @param JsonStrategy|null $strategy
     *
     * @return array
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        return $strategy ? $strategy->toJson($this) : $this->toArray();
    }
}
