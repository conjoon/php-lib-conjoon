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
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Class AbstractMailFolderTest
 * @package Tests\Conjoon\MailClient\Folder
 */
class AbstractMailFolderTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testConstructor()
    {

        $name = "INBOX.Some Folder";
        $unreadMessages = 23;
        $totalMessages = 100;

        $folderKey = new FolderKey("dev", $name);
        $abstractMailFolder = $this->createMailFolder(
            $folderKey,
            [
            "unreadMessages" => $unreadMessages,
            "totalMessages"  => $totalMessages,
            "name" => $name
            ]
        );

        $this->assertInstanceOf(AbstractMailFolder::class, $abstractMailFolder);

        $this->assertSame($folderKey, $abstractMailFolder->getFolderKey());
        $this->assertSame($name, $abstractMailFolder->getName());
        $this->assertSame($unreadMessages, $abstractMailFolder->getUnreadMessages());
        $this->assertSame($totalMessages, $abstractMailFolder->getTotalMessages());
    }


    /**
     * Tests constructor with exception for missing unreadMessages never thrown
     */
    public function testConstructorExceptionUnreadCountNotThrown()
    {
        $folderKey = new FolderKey("dev", "TEST");
        $folder = $this->createMailFolder(
            $folderKey,
            [
            "name" => "TEST"
            ]
        );

        $this->assertNull($folder->getUnreadMessages());
        $this->assertNull($folder->getTotalMessages());
    }


    /**
     * Tests constructor with exception for missing name neevr thrown
     */
    public function testConstructorExceptionNameNotThrown()
    {
        $folderKey = new FolderKey("dev", "TEST");
        $folder = $this->createMailFolder(
            $folderKey,
            [
            "unreadMessages" => 0,
            "totalMessages" => 0
            ]
        );


        $this->assertNull($folder->getName());
    }

    /**
     * Tests constructor with exception for missing totalMessages never thrown
     */
    public function testConstructorExceptionTotalMessagesNotThrown()
    {
        $folderKey = new FolderKey("dev", "TEST");
        $folder = $this->createMailFolder(
            $folderKey,
            [
                "unreadMessages" => 0,
                "name" => "INBOX"
            ]
        );

        $this->assertNull($folder->getTotalMessages());
    }


// ---------------------
//    Helper Functions
// ---------------------


    /**
     * Returns an anonymous class extending AbstractMailFolder.
     * @param FodlerKey $key
     * @param array|null $data
     * @return AbstractMailFolder
     */
    protected function createMailFolder(FolderKey $key, array $data = null): AbstractMailFolder
    {
        // Create a new instance from the Abstract Class
        return new class ($key, $data) extends AbstractMailFolder {
        };
    }
}
