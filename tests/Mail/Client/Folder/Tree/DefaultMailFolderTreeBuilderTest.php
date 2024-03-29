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

namespace Tests\Conjoon\Mail\Client\Folder\Tree;

use Conjoon\Mail\Client\Data\CompoundKey\FolderKey;
use Conjoon\Mail\Client\Folder\ListMailFolder;
use Conjoon\Mail\Client\Folder\MailFolder;
use Conjoon\Mail\Client\Folder\MailFolderList;
use Conjoon\Mail\Client\Folder\Tree\DefaultMailFolderTreeBuilder;
use Conjoon\Mail\Client\Folder\Tree\MailFolderTreeBuilder;
use Conjoon\Mail\Client\Imap\Util\DefaultFolderIdToTypeMapper;
use Tests\TestCase;

/**
 * Class DefaultMailFolderTreeBuilderTest
 * @package Tests\Conjoon\Mail\Client\Folder\Tree
 */
class DefaultMailFolderTreeBuilderTest extends TestCase
{
    /**
     * Tests constructor and base class.
     */
    public function testInstance()
    {

        $builder = $this->createBuilder();
        $this->assertInstanceOf(MailFolderTreeBuilder::class, $builder);
    }


    /**
     * Tests listToTre
     */
    public function testListToTreeInbox()
    {

        $builder = $this->createBuilder();

        $mailFolderList = $this->createMailFolderList(
            ["INBOX",
                "INBOX.Drafts",
                "INBOX.Drafts.Revision",
                "INBOX.Drafts.Revision.TlDr",
                ["INBOX.Drafts.Revision.SkipMe", ["attributes" => ["\\noselect"]]],
                "INBOX.Drafts.Revision.TlNs",

                "INBOX.Sent",
                "STUFF",
                "STUFF.Folder"]
        );

        $mailFolderChildList = $builder->listToTree($mailFolderList, ["INBOX"]);

        $this->assertSame(3, count($mailFolderChildList));

        $mailFolder = $mailFolderChildList[0];
        $this->assertSame("INBOX", $mailFolder->getName());
        $this->assertSame("INBOX", $mailFolder->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_INBOX, $mailFolder->getFolderType());
        $children = $mailFolder->getData();
        $this->assertSame(0, count($children));

        $drafts = $mailFolderChildList[1];
        $this->assertSame("Drafts", $drafts->getName());
        $this->assertSame("INBOX.Drafts", $drafts->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_DRAFT, $drafts->getFolderType());
        $children = $drafts->getData();
        $this->assertSame(1, count($children));

        $revision = $drafts->getData()[0];
        $this->assertSame("Revision", $revision->getName());
        $this->assertSame("INBOX.Drafts.Revision", $revision->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_FOLDER, $revision->getFolderType());
        $children = $revision->getData();
        $this->assertSame(2, count($children));

        $tldr = $revision->getData()[0];
        $this->assertSame("TlDr", $tldr->getName());
        $this->assertSame("INBOX.Drafts.Revision.TlDr", $tldr->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_FOLDER, $tldr->getFolderType());
        $children = $tldr->getData();
        $this->assertSame(0, count($children));

        $tlns = $revision->getData()[1];
        $this->assertSame("TlNs", $tlns->getName());
        $this->assertSame("INBOX.Drafts.Revision.TlNs", $tlns->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_FOLDER, $tlns->getFolderType());
        $children = $tlns->getData();
        $this->assertSame(0, count($children));

        $sent = $mailFolderChildList[2];
        $this->assertSame("Sent", $sent->getName());
        $this->assertSame("INBOX.Sent", $sent->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_SENT, $sent->getFolderType());
        $children = $sent->getData();
        $this->assertSame(0, count($children));
    }


    /**
     * Tests listToTre
     */
    public function testListToTreeStuff()
    {

        $builder = $this->createBuilder();

        $mailFolderList = $this->createMailFolderList(
            ["INBOX",
                "INBOX.Drafts",
                "INBOX.Drafts.Revision",
                "INBOX.Drafts.Revision.TlDr",
                ["INBOX.Drafts.Revision.SkipMe", ["attributes" => ["\\noselect"]]],
                "INBOX.Drafts.Revision.TlNs",

                "INBOX.Sent",
                "STUFF",
                "STUFF.Folder"]
        );

        $mailFolderChildList = $builder->listToTree($mailFolderList, ["STUFF"]);

        $this->assertSame(2, count($mailFolderChildList));

        $stuff = $mailFolderChildList[0];
        $this->assertSame("STUFF", $stuff->getName());
        $this->assertSame("STUFF", $stuff->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_FOLDER, $stuff->getFolderType());
        $children = $stuff->getData();
        $this->assertSame(0, count($children));

        $stuffFolder = $mailFolderChildList[1];
        $this->assertSame("Folder", $stuffFolder->getName());
        $this->assertSame("STUFF.Folder", $stuffFolder->getFolderKey()->getId());
        $this->assertSame(MailFolder::TYPE_FOLDER, $stuffFolder->getFolderType());
        $children = $stuffFolder->getData();
        $this->assertSame(0, count($children));
    }


    /**
     * Tests sortMailFolders
     */
    public function testSortMailFolders()
    {

        $builder = $this->createBuilder();

        $mailFolderList = $this->createMailFolderList(
            [
                "Junk",
                "INBOX",
                "Drafts",
                "INBOX.Sent",
                "STUFF",
                "TRASH",
                "STUFF.Folder"
            ]
        );

        $mailFolderChildList = $builder->listToTree($mailFolderList, []);

        $this->assertSame(5, count($mailFolderChildList));

        $mailFolder = $mailFolderChildList[0];
        $this->assertSame("INBOX", $mailFolder->getName());
        $mailFolder = $mailFolderChildList[1];
        $this->assertSame("Drafts", $mailFolder->getName());
        $mailFolder = $mailFolderChildList[2];
        $this->assertSame("Junk", $mailFolder->getName());
        $mailFolder = $mailFolderChildList[3];
        $this->assertSame("TRASH", $mailFolder->getName());
        $mailFolder = $mailFolderChildList[4];
        $this->assertSame("STUFF", $mailFolder->getName());
    }

// -------------------------------
//  Helper
// -------------------------------

    /**
     * @return MailFolderList
     */
    protected function createMailFolderList($data): MailFolderList
    {

        $mailFolderList = new MailFolderList();

        foreach ($data as $item) {
            $cData = [];

            if (is_array($item)) {
                $cData = $item[1];
                $item = $item[0];
            }

            $delimiter = ".";


            $parts = explode($delimiter, $item);
            $listMailFolder = new ListMailFolder(
                new FolderKey("dev", $item),
                array_merge(["name" => array_pop($parts),
                    "unreadCount" => 0,
                    "delimiter" => $delimiter], $cData)
            );

            $mailFolderList[] = $listMailFolder;
        }

        return $mailFolderList;
    }

    /**
     * @return DefaultMailFolderTreeBuilder
     */
    protected function createBuilder(): DefaultMailFolderTreeBuilder
    {

        return new DefaultMailFolderTreeBuilder(
            $this->createMapper()
        );
    }


    /**
     * @param string $id
     * @param string $delimiter
     * @return ListMailFolder
     */
    public function createListMailFolder(string $id, string $delimiter): ListMailFolder
    {

        $parts = explode($id, $delimiter);

        return new ListMailFolder(
            new FolderKey("dev", $id),
            ["name" => array_pop($parts),
                "delimiter" => $delimiter,
                "unreadCount" => 0]
        );
    }


    /**
     * @return DefaultFolderIdToTypeMapper
     */
    protected function createMapper(): DefaultFolderIdToTypeMapper
    {
        return new DefaultFolderIdToTypeMapper();
    }


    /**
     * @param $mid
     * @param $id
     * @return FolderKey
     */
    protected function createFolderKey($mid, $id): FolderKey
    {
        return new FolderKey($mid, $id);
    }
}
