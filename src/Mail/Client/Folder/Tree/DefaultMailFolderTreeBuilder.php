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

namespace Conjoon\Mail\Client\Folder\Tree;

use Conjoon\Mail\Client\Folder\FolderIdToTypeMapper;
use Conjoon\Mail\Client\Folder\ListMailFolder;
use Conjoon\Mail\Client\Folder\MailFolder;
use Conjoon\Mail\Client\Folder\MailFolderChildList;
use Conjoon\Mail\Client\Folder\MailFolderList;

/**
 * Class DefaultMailFolderTreeBuilder.
 * Default implementation for a MailFolderTreeBuilder.
 *
 *
 * @package Conjoon\Mail\Client\Folder\Tree
 */
class DefaultMailFolderTreeBuilder implements MailFolderTreeBuilder
{
    /**
     * @var FolderIdToTypeMapper
     */
    protected FolderIdToTypeMapper $folderIdToTypeMapper;


    /**
     * DefaultMailFolderTreeBuilder constructor.
     * @param FolderIdToTypeMapper $folderIdToTypeMapper
     */
    public function __construct(FolderIdToTypeMapper $folderIdToTypeMapper)
    {
        $this->folderIdToTypeMapper = $folderIdToTypeMapper;
    }


    /**
     * @return FolderIdToTypeMapper
     */
    public function getFolderIdToTypeMapper(): FolderIdToTypeMapper
    {
        return $this->folderIdToTypeMapper;
    }


// +-------------------------------
// | MailFolderTreeBuilder
// +-------------------------------

    /**
     * @inheritdoc
     */
    public function listToTree(MailFolderList $mailFolderList, array $root): MailFolderChildList
    {

        $folders = [];

        $systemFolderTypes = [];

        foreach ($mailFolderList as $mailbox) {
            if ($this->shouldSkipMailFolder($mailbox, $root)) {
                continue;
            }

            $parts = explode($mailbox->getDelimiter(), $mailbox->getFolderKey()->getId());
            array_pop($parts);
            $nameParts = explode($mailbox->getDelimiter(), $mailbox->getName());
            $name = array_pop($nameParts);


            $folderType = $this->getFolderIdToTypeMapper()->getFolderType($mailbox);

            if (in_array($folderType, $systemFolderTypes)) {
                $folderType = MailFolder::TYPE_FOLDER;
            }

            $mailFolder = new MailFolder(
                $mailbox->getFolderKey(),
                ["name" => $name,
                    "unreadCount" => $mailbox->getUnreadCount(),
                    "folderType" => $folderType]
            );

            if ($folderType !== MailFolder::TYPE_FOLDER) {
                $systemFolderTypes[] = $folderType;
            }

            $parentKey = strtolower(implode($mailbox->getDelimiter(), $parts));
            if (!isset($folders[$parentKey])) {
                $folders[$parentKey] = [];
            }
            $folders[$parentKey][] = $mailFolder;
        }

        $mailFolderChildList = new MailFolderChildList();

        foreach ($folders as $parentKey => $mailFolders) {
            $mailFolders = $this->sortMailFolders($mailFolders);

            $tmp = $this->getMailFolderWithId($parentKey, $folders);
            foreach ($mailFolders as $item) {
                foreach ($root as $rootId) {
                    if (strtolower($parentKey) === strtolower($rootId)) {
                        $mailFolderChildList[] = $item;
                        continue 2;
                    }
                }
                if (!$tmp) {
                    $mailFolderChildList[] = $item;
                } else {
                    $tmp->addMailFolder($item);
                }
            }
        }

        return $mailFolderChildList;
    }


// +-------------------------------
// | Helper
// +-------------------------------

    /**
     * Sorts the mailfolders given their type, if available, and will
     * place INBOX, DRAFT, JUNK, SENT and TRASH folders at the firs
     * indexes, in this order.
     *
     * @param $mailFolders
     *
     * @return mixed
     */
    private function sortMailFolders($mailFolders)
    {

        $findType = function ($types) use ($mailFolders) {
            $found = array_filter(
                $mailFolders,
                fn($folder) => array_search($folder->getFolderType(), $types) !== false
            );
            return $found;
        };

        $mailFolders = array_merge(
            $findType([MailFolder::TYPE_INBOX]),
            $findType([MailFolder::TYPE_DRAFT]),
            $findType([MailFolder::TYPE_JUNK]),
            $findType([MailFolder::TYPE_SENT]),
            $findType([MailFolder::TYPE_TRASH]),
            $findType([MailFolder::TYPE_FOLDER])
        );

        return $mailFolders;
    }

    /**
     * Looks up the folder with the specified id in the list of MailFolders.
     *
     * @param string $id
     * @param array $folders
     *
     * @return MailFolder
     */
    private function getMailFolderWithId(string $id, array $folders): ?MailFolder
    {

        foreach ($folders as $folderList) {
            foreach ($folderList as $item) {
                if (strtolower($item->getFolderKey()->getId()) === strtolower($id)) {
                    return $item;
                }
            }
        }

        return null;
    }


    /**
     * Returns true if the specified MailFolder should be ignored,
     * which is true if either the \noselect or \nonexistent attribute
     * is set for this ListMailFolder, or if the id of the Mailbox does not indicate
     * a child relationship with the specified $root id.
     *
     * @param ListMailFolder $listMailFolder
     * @param array $root
     *
     * @return boolean
     */
    protected function shouldSkipMailFolder(ListMailFolder $listMailFolder, array $root): bool
    {

        $id = $listMailFolder->getFolderKey()->getId();

        $idParts = explode($listMailFolder->getDelimiter(), $id);

        if (count($root)) {
            $skip = 0;
            foreach ($root as $globalIds) {
                $rootParts = explode($listMailFolder->getDelimiter(), $globalIds);
                foreach ($rootParts as $key => $rootId) {
                    if (!isset($idParts[$key]) || $rootId !== $idParts[$key]) {
                        $skip++;
                    }
                }
            }
            if ($skip === count($root)) {
                return true;
            }
        }

        return in_array("\\noselect", $listMailFolder->getAttributes()) ||
            in_array("\\nonexistent", $listMailFolder->getAttributes());
    }
}
