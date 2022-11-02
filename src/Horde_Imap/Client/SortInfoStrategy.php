<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Horde_Imap\Client;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Data\Sort\SortDirection;
use Conjoon\Data\Sort\SortInfo;
use Conjoon\Data\Sort\SortInfoList;
use Horde_Imap_Client;

/**
 * Implements JsonStrategy for transforming SortInfoStrategy into array of entries
 * the Horde-API understands.
 *
 * @example
 *
 *    $sortInfoList = new SortInfoList();
 *    $sortInfoList[] = new SortInfo("subject", SortDirection::DESC);
 *    $sortInfoList[] = new SortInfo("date", SortDirection::ASC);
 *    $sortInfoList[] = new SortInfo("size", SortDirection::DESC);
 *
 *    $strategy = new SortInfoStrategy();
 *    $strategy->toJson($sortInfoList);
 *    // produces
 *    // [
 *    //   Horde_Imap_Client::SORT_REVERSE,
 *    //   Horde_Imap_Client::SORT_SUBJECT,
 *    //   Horde_Imap_Client::SORT_DATE,
 *    //   Horde_Imap_Client::SORT_REVERSE,
 *    //   Horde_Imap_Client::SORT_SIZE
 *    // ]
 *
 */
class SortInfoStrategy implements JsonStrategy
{
    /**
     * @inheritdoc
     */
    public function toJson(Arrayable $source): array
    {
        $data = [];
        if ($source instanceof SortInfoList) {
            foreach ($source as $sortInfo) {
                $data = array_merge($data, $sortInfo->toJson($this));
            }
        }

        if ($source instanceof SortInfo) {
            if ($source->getDirection() === SortDirection::DESC) {
                $data[] = Horde_Imap_Client::SORT_REVERSE;
            }

            switch ($source->getField()) {
                case "subject":
                    $data[] = Horde_Imap_Client::SORT_SUBJECT;
                    break;
                case "to":
                    $data[] = Horde_Imap_Client::SORT_TO;
                    break;
                case "from":
                    $data[] = Horde_Imap_Client::SORT_FROM;
                    break;
                case "date":
                    $data[] = Horde_Imap_Client::SORT_DATE;
                    break;
                case "size":
                    $data[] = Horde_Imap_Client::SORT_SIZE;
                    break;
            }
        }

        return $data;
    }
}
