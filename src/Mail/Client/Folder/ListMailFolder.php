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

namespace Conjoon\Mail\Client\Folder;

use Conjoon\Mail\Client\Data\CompoundKey\FolderKey;
use InvalidArgumentException;

/**
 * Class ListMailFolder models MailFolder-information for a specified MailAccount,
 * including delimiter property.
 *
 * @example
 *
 *    $item = new ListMailFolder(
 *              new FolderKey("dev", "INBOX.SomeFolder"),
 *              [
 *                 "name"        => "INBOX.Some Folder",
 *                 "delimiter"   => "."
 *                 "unreadCount" => 4
 *              ]
 *            );
 *
 *    $listMailFolder->getDelimiter(); // "."
 *    $item->getUnreadCount(4);
 *
 *
 *
 * @package Conjoon\Mail\Client\Folder
 */
class ListMailFolder extends AbstractMailFolder
{
    /**
     * @var string
     */
    protected string $delimiter;

    /**
     * @var array
     */
    protected array $attributes;


    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException if delimiter in $data is missing
     */
    public function __construct(FolderKey $folderKey, array $data)
    {

        if (!isset($data["delimiter"])) {
            throw new InvalidArgumentException(
                "value for property \"delimiter\" missing"
            );
        }

        if (!isset($data["attributes"])) {
            $data["attributes"] = [];
        }

        parent::__construct($folderKey, $data);
    }


    /**
     * Sets the delimiter for this ListMailFolder.
     *
     * @param string $delimiter
     */
    protected function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }


    /**
     * Returns the delimiter for this ListMailFolder.
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }


    /**
     * Sets the attributes for this ListMailFolder.
     *
     * @param array $attributes
     */
    protected function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }


    /**
     * Returns the attributes for this ListMailFolder.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
