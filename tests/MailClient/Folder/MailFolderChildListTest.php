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
use Conjoon\MailClient\Folder\MailFolder;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\Core\Util\AbstractList;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;
use Tests\TestCase;

/**
 * Class MailFolderChildListTest
 * @package Tests\Conjoon\MailClient\Folder
 */
class MailFolderChildListTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass()
    {

        $mailFolderChildList = new MailFolderChildList();
        $this->assertInstanceOf(AbstractList::class, $mailFolderChildList);
        $this->assertInstanceOf(Arrayable::class, $mailFolderChildList);
        $this->assertInstanceOf(Jsonable::class, $mailFolderChildList);

        $this->assertSame(MailFolder::class, $mailFolderChildList->getEntityType());
    }


    /**
     * Tests constructor
     */
    public function testToArray()
    {

        $data = [
            "name" => "INBOX",
            "unreadMessages" => 5,
            "totalMessages" => 10,
            "folderType" => MailFolder::TYPE_INBOX
        ];

        $folder = new MailFolder(
            new FolderKey("dev", "INBOX"),
            $data
        );

        $mailFolderChildList = new MailFolderChildList();

        $mailFolderChildList[] = $folder;

        $this->assertEquals([[
            "type" => "MailFolder",
            "mailAccountId" => "dev",
            "id" => "INBOX",
            "folderType" => MailFolder::TYPE_INBOX,
            "unreadMessages" => 5,
            "totalMessages" => 10,
            "name" => "INBOX",
            "data" => []
        ]], $mailFolderChildList->toArray());
    }


    /**
     * Tests constructor
     */
    public function testToJson()
    {

        $data = [
            "name" => "INBOX",
            "unreadMessages" => 5,
            "totalMessages" => 10,
            "folderType" => MailFolder::TYPE_INBOX
        ];

        $folder = new MailFolder(
            new FolderKey("dev", "INBOX"),
            $data
        );

        $mailFolderChildList = new MailFolderChildList();
        $mailFolderChildList[] = $folder;

        $this->assertEquals(
            $mailFolderChildList->toArray(),
            $mailFolderChildList->toJson()
        );

        // w/ strategy
        $strategyMock =
            $this->getMockBuilder(JsonStrategy::class)
                ->getMockForAbstractClass();

        $strategyMock
            ->expects($this->exactly(1))
            ->method("toJson")
            ->with($mailFolderChildList)
            ->willReturn($mailFolderChildList->toArray());

        $this->assertEquals($mailFolderChildList->toArray(), $mailFolderChildList->toJson($strategyMock));
    }
}
