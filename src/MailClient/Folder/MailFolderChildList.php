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

namespace Conjoon\MailClient\Folder;

use Conjoon\Core\Util\AbstractList;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;

/**
 * Class MailFolderList organizes a list of ListMailFolders.
 *
 * @example
 *
 *    $list = new MailFolderChildList();
 *
 *    $mailFolder = new MailFolder(
 *      new FolderKey("dev", "INBOX"), ["name" => "INBOX", "unreadMessages" => 23, "totalMessages" => 100]
 *    );
 *    $list[] = $listMailFolder;
 *
 *    foreach ($list as $key => $mItem) {
 *        // iterating over the item
 *    }
 *
 * @package Conjoon\MailClient\Folder
 */
class MailFolderChildList extends AbstractList implements Arrayable, Jsonable
{
// -------------------------
//  AbstractList
// -------------------------

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return MailFolder::class;
    }


// -------------------------
//  Arrayable, Jsonable
// -------------------------

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {

        $data = [];

        foreach ($this->data as $mailFolder) {
            $data[] = $mailFolder->toArray();
        }

        return $data;
    }


    /**
     * @inheritdoc
     */
    public function toJson(JsonStrategy $jsonStrategy = null): array
    {
        return $jsonStrategy ? $jsonStrategy->toJson($this) : $this->toArray();
    }
}
