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

namespace Tests\Conjoon\MailClient\Folder;

use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Folder\AbstractMailFolder;
use Conjoon\MailClient\Folder\MailFolder;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\Util\JsonApiStrategy;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Class MailFolderTest
 * @package Tests\Conjoon\MailClient\Folder
 */
class MailFolderTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testConstructor()
    {

        $folderKey = $this->createKey();

        $this->assertTrue(MailFolder::TYPE_INBOX != "");
        $this->assertTrue(MailFolder::TYPE_DRAFT != "");
        $this->assertTrue(MailFolder::TYPE_JUNK != "");
        $this->assertTrue(MailFolder::TYPE_TRASH != "");
        $this->assertTrue(MailFolder::TYPE_SENT != "");
        $this->assertTrue(MailFolder::TYPE_FOLDER != "");

        $mailFolder = new MailFolder(
            $folderKey,
            [
            "name" => "Folder",
            "unreadMessages" => 0,
            "totalMessages" => 0,
            "folderType" => MailFolder::TYPE_INBOX
            ]
        );

        $this->assertInstanceOf(AbstractMailFolder::class, $mailFolder);
        $this->assertSame(MailFolder::TYPE_INBOX, $mailFolder->getFolderType());


        $children = $mailFolder->getData();
        $this->assertInstanceOf(MailFolderChildList::class, $mailFolder->getData());
        $this->assertSame(0, count($children));


        $mf = new MailFolder($this->createKey(), [
            "name" => "Folder2",
            "unreadMessages" => 32,
            "totalMessages" => 45,
            "folderType" => MailFolder::TYPE_INBOX
        ]);
        $ret = $mailFolder->addMailFolder($mf);

        $this->assertSame($mf, $ret);

        $this->assertSame(1, count($children));
        $this->assertSame($mf, $children[0]);
    }


    /**
     * Tests constructor with exception for missing folderType never thrown
     */
    public function testConstructorExceptionFolderTypeNeverThrown()
    {

        $folderKey = $this->createKey();
        $folder = new MailFolder(
            $folderKey,
            [
            ]
        );

        $this->assertNull($folder->getFolderType());
    }


    /**
     * Test for exception if invalid folder type gets submitted to setFolderType
     */
    public function testSetFolderTypeException()
    {
        $this->expectException(InvalidArgumentException::class);

        new MailFolder($this->createKey(), ["folderType" => "foo"]);
    }


    /**
     * Test for toArray
     */
    public function testToArray()
    {

        $data = [
            "unreadMessages" => 5,
            "totalMessages" => 8
        ];

        $key = $this->createKey("dev", "INBOX");

        $mf = new MailFolder($key, $data);

        $mf->addMailFolder(
            new MailFolder(
                $this->createKey("dev", "INBOX.SubFolder"),
                [
                    "name" => "A",
                    "unreadMessages" => 4,
                    "totalMessages" => 2,
                    "folderType" => MailFolder::TYPE_INBOX
                ]
            )
        );

        $json = $mf->toJson();

        $expected = array_merge($key->toArray(), $data, ["type" => "MailFolder"]);
        $expected["data"] = [[
            "mailAccountId" => "dev",
            "type" => "MailFolder",
            "id" => "INBOX.SubFolder",
            "name" => "A",
            "unreadMessages" => 4,
            "totalMessages" => 2,
            "folderType" => MailFolder::TYPE_INBOX,
            "data" => []
        ]];


        $this->assertEquals($expected, $json);
    }


    /**
     * Test for toJson
     */
    public function testToJson()
    {
        $strategy = new JsonApiStrategy();

        $data = [
            "folderType" => MailFolder::TYPE_INBOX,
            "name" => "INBOX",
            "unreadMessages" => 5,
            "totalMessages" => 400
        ];

        $key = $this->createKey("dev", "INBOX");

        $mf = new MailFolder($key, $data);

        $mf->addMailFolder(
            new MailFolder(
                $this->createKey("dev", "INBOX.SubFolder"),
                [
                    "name" => "A",
                    "unreadMessages" => 4,
                    "totalMessages" => 23,
                    "folderType" => MailFolder::TYPE_INBOX
                ]
            )
        )->addMailFolder(
            new MailFolder(
                $this->createKey("dev", "INBOX.SubFolder.last week"),
                [
                    "name" => "last week",
                    "unreadMessages" => 0,
                    "totalMessages" => 0,
                    "folderType" => MailFolder::TYPE_INBOX
                ]
            )
        );

        $json = $mf->toJson($strategy);

        $relationships = [
            "MailAccount" => [
                "data" => [
                    "id" => "dev",
                    "type" => "MailAccount"
                ]
            ]
        ];

        $expected = [
            "id" => $key->getId(),
            "type" => "MailFolder",
            "attributes" => $data,
            "relationships" => $relationships
        ];

        $expected["attributes"]["data"] = [[
            "id" => "INBOX.SubFolder",
            "type" => "MailFolder",
            "attributes" => [
                "name" => "A",
                "unreadMessages" => 4,
                "totalMessages" => 23,
                "folderType" => MailFolder::TYPE_INBOX,
                "data" => [[

                    "id" => "INBOX.SubFolder.last week",
                    "type" => "MailFolder",
                    "attributes" => [
                        "name" => "last week",
                        "unreadMessages" => 0,
                        "totalMessages" => 0,
                        "folderType" => MailFolder::TYPE_INBOX,
                        "data" => []
                    ],
                    "relationships" => [
                        "MailAccount" => [
                            "data" => [
                                "id" => "dev",
                                "type" => "MailAccount"
                            ]
                        ]
                    ]

                ]]
            ],
            "relationships" => [
                "MailAccount" => [
                    "data" => [
                        "id" => "dev",
                        "type" => "MailAccount"
                    ]
                ]
            ]
        ]];


        $this->assertEquals($expected, $json);
    }


    /**
     * Tests behavior of MailFolder with data
     */
    public function testData()
    {
        $key = $this->createKey("dev", "INBOX");

        $addFolder = new MailFolder(new FolderKey("dev", "INBOX.Drafts"), []);

        $make = fn ($data) => new MailFolder($key, $data);

        $this->assertInstanceOf(MailFolderChildList::class, $make([])->getData());

        $mf = $make(["data" => $addFolder]);
        $this->assertInstanceOf(MailFolderChildList::class, $mf->getData());
        $this->assertSame($addFolder, $mf->getData()[0]);
        $this->assertArrayHasKey("data", $mf->toArray());

        $mf = $make(["data" => null]);
        $this->assertNull($mf->getData());
        $this->assertArrayNotHasKey("data", $mf->toArray());

        $mf = $make([]);
        $mf->setData(null);
        $this->assertNull($mf->getData());
        $this->assertArrayNotHasKey("data", $mf->toArray());
    }


// ---------------------
//    Helper
// ---------------------


    /**
     * @return FolderKey
     */
    public function createKey($account = "dev", $name = "INBOX.Some Folder")
    {

        return new FolderKey($account, $name);
    }
}
