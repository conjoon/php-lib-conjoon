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

namespace Conjoon\Mail\Client\Service;

use Conjoon\Mail\Client\Attachment\FileAttachmentItemList;
use Conjoon\Mail\Client\Attachment\FileAttachmentList;
use Conjoon\Mail\Client\Attachment\Processor\FileAttachmentProcessor;
use Conjoon\Mail\Client\Data\CompoundKey\MessageKey;
use Conjoon\Mail\Client\Data\CompoundKey\AttachmentKey;
use Conjoon\Mail\Client\MailClient;

/**
 * class DefaultAttachmentService
 *
 * @package Conjoon\Mail\Client\Service
 */
class DefaultAttachmentService implements AttachmentService
{
    /**
     * @var MailClient
     */
    protected MailClient $mailClient;

    /**
     * @var FileAttachmentProcessor
     */
    protected FileAttachmentProcessor $fileAttachmentProcessor;


    /**
     * DefaultAttachmentService constructor.
     *
     * @param MailClient $mailClient
     * @param FileAttachmentProcessor $fileAttachmentProcessor
     */
    public function __construct(MailClient $mailClient, FileAttachmentProcessor $fileAttachmentProcessor)
    {
        $this->mailClient = $mailClient;
        $this->fileAttachmentProcessor = $fileAttachmentProcessor;
    }


    /**
     * Returns the FileAttachmentProcessor used by this class.
     *
     * @return FileAttachmentProcessor
     */
    public function getFileAttachmentProcessor(): FileAttachmentProcessor
    {
        return $this->fileAttachmentProcessor;
    }


// +---------------------------------
// | AttachmentService
// +---------------------------------

    /**
     * @inheritdoc
     */
    public function createAttachments(
        MessageKey $messageKey,
        FileAttachmentList $fileAttachments
    ): FileAttachmentItemList {
        $attachments = $this->getMailClient()->createAttachments($messageKey, $fileAttachments);

        return $this->processFileAttachments($attachments);
    }


    /**
     * @inheritdoc
     */
    public function deleteAttachment(AttachmentKey $attachmentKey): MessageKey
    {
        return $this->getMailClient()->deleteAttachment($attachmentKey);
    }



    /**
     * @inheritdoc
     */
    public function getFileAttachmentItemList(MessageKey $key): FileAttachmentItemList
    {
        $attachments = $this->getMailClient()->getFileAttachmentList($key);

        return $this->processFileAttachments($attachments);
    }


    /**
     * Returns the MailClient used by this Attachment.
     *
     * @return MailClient
     */
    public function getMailClient(): MailClient
    {
        return $this->mailClient;
    }


    /**
     * Returns the attachmentList converted to a FileAttachmentItemList.
     *
     * @param FileAttachmentList $attachmentList
     *
     * @return FileAttachmentItemList
     */
    protected function processFileAttachments(FileAttachmentList $attachmentList): FileAttachmentItemList
    {
        $itemList = new FileAttachmentItemList();

        $processor = $this->getFileAttachmentProcessor();

        foreach ($attachmentList as $attachment) {
            $itemList[] = $processor->process($attachment);
        }

        return $itemList;
    }
}
