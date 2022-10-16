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

namespace Tests\Conjoon\MailClient\Message\Attachment;

use Conjoon\MailClient\Message\Attachment\FileAttachmentItem;
use Conjoon\MailClient\Message\Attachment\FileAttachmentItemList;
use Conjoon\MailClient\Data\CompoundKey\AttachmentKey;
use Conjoon\Core\Util\AbstractList;
use Conjoon\Core\Contract\Jsonable;
use Tests\TestCase;

/**
 * Class FileAttachmentItemListTest
 * @package Tests\Conjoon\MailClient\Message\Attachment
 */
class FileAttachmentItemListTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass()
    {

        $attachmentList = new FileAttachmentItemList();
        $this->assertInstanceOf(AbstractList::class, $attachmentList);
        $this->assertInstanceOf(Jsonable::class, $attachmentList);

        $this->assertSame(FileAttachmentItem::class, $attachmentList->getEntityType());
    }


    /**
     * Test toJson
     */
    public function testToJson()
    {

        $attachment1 = $this->createAttachment();
        $attachment2 = $this->createAttachment();

        $attachmentList = new FileAttachmentItemList();
        $attachmentList[] = $attachment1;
        $attachmentList[] = $attachment2;

        $this->assertSame([
            $attachment1->toJson(),
            $attachment2->toJson(),
        ], $attachmentList->toJson());
    }


// ---------------------
//    Helper
// ---------------------

    /**
     * @return FileAttachmentItem
     */
    protected function createAttachment(): FileAttachmentItem
    {

        return new FileAttachmentItem(
            new AttachmentKey("dev", "INBOX", "123", "1"),
            [
                "type"          => "1",
                 "text"          => "2",
                 "size"          => 3,
                 "downloadUrl"   => "4",
                 "previewImgSrc" => "5"
            ]
        );
    }
}
