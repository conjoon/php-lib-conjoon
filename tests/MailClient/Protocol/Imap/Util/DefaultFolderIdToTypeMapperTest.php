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

namespace Tests\Conjoon\MailClient\Data\Protocol\Imap\Util;

use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Folder\FolderIdToTypeMapper;
use Conjoon\MailClient\Folder\ListMailFolder;
use Conjoon\MailClient\Folder\MailFolder;
use Conjoon\MailClient\Data\Protocol\Imap\Util\DefaultFolderIdToTypeMapper;
use Tests\TestCase;

/**
 * Class DefaultFolderIdToTypeMapperTest
 */
class DefaultFolderIdToTypeMapperTest extends TestCase
{
    /**
     * Tests constructor and base class.
     */
    public function testInstance()
    {

        $mapper = $this->createMapper();
        $this->assertInstanceOf(FolderIdToTypeMapper::class, $mapper);
    }


    /**
     * Tests getFolderType
     */
    public function testGetFolderType()
    {

        $mapper = $this->createMapper();

        $this->assertSame(MailFolder::TYPE_DRAFT, $mapper->getFolderType(
            $this->createListMailFolder("Entwürfe", "/")
        ));

        // +--------------------
        // | INBOX
        // +-------------------
        $this->assertSame(
            MailFolder::TYPE_INBOX,
            $mapper->getFolderType($this->createListMailFolder("INBOX", ".")),
        );
        $this->assertSame(
            MailFolder::TYPE_INBOX,
            $mapper->getFolderType($this->createListMailFolder("POSTEINGANG", ".")),
        );

        // DRAFTS
        $this->assertSame(
            MailFolder::TYPE_DRAFT,
            $mapper->getFolderType($this->createListMailFolder("INBOX.Drafts", "."))
        );
        $this->assertSame(
            MailFolder::TYPE_DRAFT,
            $mapper->getFolderType($this->createListMailFolder("INBOX-DRAFTS", "-"))
        );
        $this->assertSame(
            MailFolder::TYPE_DRAFT,
            $mapper->getFolderType($this->createListMailFolder("Draft", "-"))
        );
        $this->assertSame(
            MailFolder::TYPE_DRAFT,
            $mapper->getFolderType($this->createListMailFolder("Entwurf", "/"))
        );


        // TRASH
        $this->assertSame(
            $mapper->getFolderType($this->createListMailFolder("TRASH", "/")),
            MailFolder::TYPE_TRASH
        );

        $this->assertSame(
            $mapper->getFolderType($this->createListMailFolder("INBOX:TRASH", ":")),
            MailFolder::TYPE_TRASH
        );

        $this->assertSame(
            $mapper->getFolderType($this->createListMailFolder("DELETED", ":")),
            MailFolder::TYPE_TRASH
        );

        $this->assertSame(
            MailFolder::TYPE_TRASH,
            $mapper->getFolderType($this->createListMailFolder("DELETED MESSAGES", "."))
        );

        $this->assertSame(
            MailFolder::TYPE_TRASH,
            $mapper->getFolderType($this->createListMailFolder("GELÖSCHT", "."))
        );
        $this->assertSame(
            MailFolder::TYPE_TRASH,
            $mapper->getFolderType($this->createListMailFolder("PapIerKorb", "."))
        );

        // JUNK
        $this->assertSame(
            MailFolder::TYPE_JUNK,
            $mapper->getFolderType(
                $this->createListMailFolder("SPAMVerDacht", ".")
            )
        );

        $this->assertSame(
            MailFolder::TYPE_JUNK,
            $mapper->getFolderType($this->createListMailFolder("Inbox.SPAMVerDacht", "."))
        );
        $this->assertSame(
            MailFolder::TYPE_JUNK,
            $mapper->getFolderType($this->createListMailFolder("Spam", "-"))
        );

        // SENT
        $this->assertSame(
            MailFolder::TYPE_SENT,
            $mapper->getFolderType($this->createListMailFolder("SENT MESSAGES", "."))
        );
        $this->assertSame(
            MailFolder::TYPE_SENT,
            $mapper->getFolderType($this->createListMailFolder("Gesendet", "."))
        );



        // FOLDER
        foreach (
            [
                ["SomeRandomFolder/Draft", "/"],
                ["SomeRandomFolder/Draft/Test", "/"],
                ["SomeRandom", "."],
                ["INBOX/Somefolder/Deep/Drafts", "/"],
                ["INBOX.Trash.Deep.Deeper.Folder", "."],
                ["Junk/Draft", "/"],
                ["TRASH.Draft.folder", "."]
            ] as $folder
        ) {
            $this->assertSame(
                MailFolder::TYPE_FOLDER,
                $mapper->getFolderType($this->createListMailFolder($folder[0], $folder[1]))
            );
        }


        // GMAIL
        foreach (["Google Mail", "Gmail"] as $label) {
            $this->assertSame(
                MailFolder::TYPE_SENT,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Gesendet", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_SENT,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Sent", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_DRAFT,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Entwürfe", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_DRAFT,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Drafts", "."))
            );
            $this->assertNotSame(
                MailFolder::TYPE_INBOX,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Alle Nachrichten", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_JUNK,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Spam", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_TRASH,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Papierkorb", "."))
            );
            $this->assertSame(
                MailFolder::TYPE_TRASH,
                $mapper->getFolderType($this->createListMailFolder("[$label]/Trash", "."))
            );
        }
    }


// -------------------------------
//  Helper
// -------------------------------

    /**
     * @param string $id
     * @param string $delimiter
     * @return ListMailFolder
     */
    public function createListMailFolder($id, $delimiter): ListMailFolder
    {

        $parts = explode($id, $delimiter);

        return new ListMailFolder(
            new FolderKey("dev", $id),
            ["name"        => array_pop($parts),
                "delimiter"   => $delimiter,
                "unreadMessages" => 0,
                "totalMessages" => 100]
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
