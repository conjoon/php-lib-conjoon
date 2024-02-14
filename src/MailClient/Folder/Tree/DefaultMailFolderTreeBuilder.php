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

namespace Conjoon\MailClient\Folder\Tree;

use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;
use Conjoon\MailClient\Folder\FolderIdToTypeMapper;
use Conjoon\MailClient\Folder\ListMailFolder;
use Conjoon\MailClient\Folder\MailFolder;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\Folder\MailFolderList;
use InvalidArgumentException;

/**
 * Class DefaultMailFolderTreeBuilder.
 * Default implementation for a MailFolderTreeBuilder.
 *
 *
 * @package Conjoon\MailClient\Folder\Tree
 */
class DefaultMailFolderTreeBuilder implements MailFolderTreeBuilder
{
    /**
     * @var FolderIdToTypeMapper
     */
    protected FolderIdToTypeMapper $folderIdToTypeMapper;

    /**
     * @var array|null
     */
    protected ?array $valueCallbacks = null;

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
    public function listToTree(
        MailFolderList $mailFolderList,
        MailFolderListQuery $query
    ): MailFolderChildList {
        $folders = [];

        $dissolveNamespaces = $query->getOptions()?->getDissolveNamespaces() ?? [];

        $fields = $query->getFields();
        $includeChildNodes = in_array("data", $fields);
        $systemFolderTypes = [];

        foreach ($mailFolderList as $mailbox) {
            if ($this->shouldSkipMailFolder($mailbox, $dissolveNamespaces, $includeChildNodes)) {
                continue;
            }

            $parts = explode($mailbox->getDelimiter(), $mailbox->getFolderKey()->getId());
            array_pop($parts);

            $fieldValueMap = [];
            $folderType = null;
            foreach ($fields as $field) {
                if ($field === "data") {
                    continue;
                }
                if ($field === "folderType") {
                    $folderType = $this->buildFolderType($mailbox, $systemFolderTypes);
                    $fieldValueMap[$field] = $folderType;
                    continue;
                }

                $fieldValueMap[$field] = $this->getValueForField($mailbox, $field);
            }

            // nullify the date field since it should not be considered
            if (!$includeChildNodes) {
                $fieldValueMap["data"] = null;
            }

            $mailFolder = new MailFolder(
                $mailbox->getFolderKey(),
                $fieldValueMap
            );

            if ($folderType !== null && $folderType !== MailFolder::TYPE_FOLDER) {
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
            $mailFolders = in_array("folderType", $fields)
                            ? $this->sortMailFolders($mailFolders)
                            : $mailFolders;

            $tmp = $this->getMailFolderWithId($parentKey, $folders);
            foreach ($mailFolders as $item) {
                foreach ($dissolveNamespaces as $namespaceId) {
                    if (strtolower($parentKey) === strtolower($namespaceId)) {
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
     * place INBOX, DRAFT, JUNK, SENT and TRASH folders at the first
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

        return array_merge(
            $findType([MailFolder::TYPE_INBOX]),
            $findType([MailFolder::TYPE_DRAFT]),
            $findType([MailFolder::TYPE_JUNK]),
            $findType([MailFolder::TYPE_SENT]),
            $findType([MailFolder::TYPE_TRASH]),
            $findType([MailFolder::TYPE_FOLDER])
        );
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
     * a child relationship with the specified $dissolveNamespaces.
     *
     * @param ListMailFolder $listMailFolder
     * @param array $dissolveNamespaces
     * @param bool $includeChildNodes if set to false, the mail folder will be skipped if
     * it is not in the list of root folders
     *
     * @return boolean
     */
    protected function shouldSkipMailFolder(ListMailFolder $listMailFolder, array $dissolveNamespaces, bool $includeChildNodes = true): bool
    {

        $delim = $listMailFolder->getDelimiter();
        $id = $listMailFolder->getFolderKey()->getId();
        $idParts = explode($delim, $id);
        $depthChild = count($idParts);

        if (!count($dissolveNamespaces) && !$includeChildNodes && $depthChild > 1) {
            return true;
        }

        foreach ($dissolveNamespaces as $namespaceId) {
            // the id of the folder is not found in the globalId, skip
            // id: JUNK.Drafts   globalId: INBOX"
            if (stripos($id, $namespaceId) === false) {
                return true;
            }

            $depthRoot = count(explode($delim, $namespaceId));
            // id is part of globalId - if no childNodes should be returned, skip
            // this one.
            if (!$includeChildNodes && $depthRoot < $depthChild) {
                return true;
            }
        }


        return in_array("\\noselect", $listMailFolder->getAttributes()) ||
            in_array("\\nonexistent", $listMailFolder->getAttributes());
    }



    /**
     * Assembles the name representative for the specified mailfolder out of its id.
     *
     * @param ListMailFolder $mailFolder
     *
     * @return string
     */
    protected function buildName(ListMailFolder $mailFolder): string
    {
        $nameParts = explode($mailFolder->getDelimiter(), $mailFolder->getName());
        return array_pop($nameParts);
    }


    /**
     * Return sthe folder type for the specified $mailFolder.
     * If the folder type was already registered in $systemFolderTypes, MailFolder::TYPE_FOLDER is used.
     *
     * @param ListMailFolder $mailFolder
     * @param array $systemFolderTypes
     *
     * @return string
     *
     * @see getFolderIdToTypeMapper()
     */
    protected function buildFolderType(ListMailFolder $mailFolder, array $systemFolderTypes): string
    {
        $folderType = $this->getFolderIdToTypeMapper()->getFolderType($mailFolder);
        if (in_array($folderType, $systemFolderTypes)) {
            $folderType = MailFolder::TYPE_FOLDER;
        }

        return $folderType;
    }


    /**
     * Returns the value for the field anme, unreadMessages or totalMessages.
     *
     * @param ListMailFolder $mailFolder
     * @param string $field
     *
     * @return mixed
     */
    protected function getValueForField(ListMailFolder $mailFolder, string $field): mixed
    {
        $fields = ["name", "unreadMessages", "totalMessages"];

        if (!in_array($field, $fields)) {
            throw new InvalidArgumentException(
                "\"$field\" must be one of " . implode(", ", $fields)
            );
        }

        if (!$this->valueCallbacks) {
            $this->valueCallbacks = [
                "name"           => fn($mailFolder) => $this->buildName($mailFolder),
                "unreadMessages" => fn($mailFolder) => $mailFolder->getUnreadMessages(),
                "totalMessages"  => fn($mailFolder) => $mailFolder->getTotalMessages()
            ];
        }


        return $this->valueCallbacks[$field]($mailFolder);
    }
}
