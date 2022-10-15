<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2020-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\MailClient;

use Conjoon\MailClient\Message\Attachment\FileAttachmentItemList;
use Conjoon\MailClient\Message\Attachment\FileAttachmentList;
use Conjoon\MailClient\Data\CompoundKey\AttachmentKey;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Resource\MessageItemQuery;
use Conjoon\MailClient\Exception\MailClientException;
use Conjoon\MailClient\Exception\MailFolderNotFoundException;
use Conjoon\MailClient\Folder\MailFolderList;
use Conjoon\MailClient\Message\Flag\FlagList;
use Conjoon\MailClient\Message\MessageBody;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\MailClient\Message\MessageItemList;
use Conjoon\MailClient\Resource\MessageItemListQuery;
use Conjoon\MailClient\Resource\MailFolderListQuery;

/**
 * Interface MailClient
 * @package Conjoon\MailClient
 */
interface MailClient
{
    /**
     * Returns a MailFolderList with ListMailFolders representing all
     * mailboxes available for the specified MailAccount.
     *
     * @param MailAccount $mailAccount
     * @param MailFolderListQuery $query An additional set of options for querying the
     * MailFolderList.
     *
     * @return MailFolderList
     *
     * @throws MailClientException if any exception occurs
     */
    public function getMailFolderList(MailAccount $mailAccount, MailFolderListQuery $query): MailFolderList;


    /**
     * Returns the specified MessageItem for the submitted arguments.
     *
     * @param MessageKey $messageKey
     * @param MessageItemQuery $query An additional set of options for querying the
     * MessageItem
     *
     * @return MessageItem|null The MessageItem or null if none found.
     *
     * @throws MailClientException if any exception occurs
     */
    public function getMessageItem(MessageKey $messageKey, MessageItemQuery $query): ?MessageItem;


    /**
     * Deletes the specified MessageItem permanently.
     *
     * @param MessageKey $messageKey
     *
     * @return bool true if deleting the message was successful, otherwise false.
     *
     * @throws MailClientException if any exception occurs
     */
    public function deleteMessage(MessageKey $messageKey): bool;


    /**
     * Returns the specified MessageItemDraft for the submitted arguments.
     *
     * @param MessageKey $messageKey
     * @param MessageItemQuery $query An additional set of options for querying the
     * MessageItemDraft
     *
     * @return MessageItemDraft|null The MessageItemDraft or null if none found.
     *
     * @throws MailClientException if any exception occurs
     */
    public function getMessageItemDraft(MessageKey $messageKey, MessageItemQuery $query): ?MessageItemDraft;


    /**
     * Tries to send the specified MessageItemDraft found under $key.
     *
     * @param MessageKey $messageKey
     * @return bool true if sending was successful, otherwise false.
     *
     * @throws MailClientException if any exception occurs, or if the message found
     * is not a Draft-Message.
     */
    public function sendMessageDraft(MessageKey $messageKey): bool;


    /**
     * Returns the specified MessageBody for the submitted arguments.
     *
     * @param MessageKey $messageKey
     *
     * @return MessageBody|null The MessageBody or null if none found.
     *
     * @throws MailClientException if any exception occurs
     */
    public function getMessageBody(MessageKey $messageKey): ?MessageBody;


    /**
     * Appends a new Message to the specified Folder with the data found in MessageItemDraft.
     * Will mark the newly created Message as a draft.
     *
     * @param FolderKey $folderKey
     * @param MessageItemDraft $messageItemDraft
     *
     * @return MessageItemDraft the created MessageItemDraft
     *
     * @throws MailClientException if any exception occurs, or of the MessageItemDraft already has
     * a MessageKey
     */
    public function createMessageDraft(FolderKey $folderKey, MessageItemDraft $messageItemDraft): MessageItemDraft;


    /**
     * Appends a new Message to the specified Folder with the data found in MessageBodyDraft.
     * Will mark the newly created Message as a draft.
     *
     * @param FolderKey $folderKey
     * @param MessageBodyDraft $messageBodyDraft
     *
     * @return MessageBodyDraft the created MessageBodyDraft
     *
     * @throws MailClientException if any exception occurs, or of the MessageBodyDraft already has
     * a MessageKey
     */
    public function createMessageBodyDraft(FolderKey $folderKey, MessageBodyDraft $messageBodyDraft): MessageBodyDraft;


    /**
     * Updates the MessageBody of the specified message.
     *
     * @param MessageBodyDraft $messageBodyDraft
     *
     * @return MessageBodyDraft the created MessageBodyDraft
     *
     * @throws MailClientException if any exception occurs, or of the MessageBodyDraft does not have a
     * MessageKey
     */
    public function updateMessageBodyDraft(MessageBodyDraft $messageBodyDraft): MessageBodyDraft;


    /**
     * Updates envelope information of a Message, if the Message is a draft Message.
     *
     * @param MessageItemDraft $messageItemDraft
     *
     * @return MessageItemDraft|null The MessageItemDraft updated, along with its MessageKey, which
     * might not equal to the MessageKey in $messageItemDraft.
     *
     * @throws MailClientException if any exception occurs
     */
    public function updateMessageDraft(MessageItemDraft $messageItemDraft): ?MessageItemDraft;


    /**
     * Returns the specified MessageList for the submitted arguments.
     *
     * @param FolderKey $folderKey
     * @param MessageItemListQuery $query An additional set of options for querying the
     * MessageList.
     *
     * @return MessageItemList
     *
     * @throws MailFolderNotFoundException|MailClientException if the specified
     * MailFolder was not found, or an exception thrown by the implementing API wrapped in a MailClientException
     */
    public function getMessageItemList(FolderKey $folderKey, MessageItemListQuery $query): MessageItemList;


    /**
     * Returns ab array keyed with "unreadMessages" and "totalMessages",
     * set to the number ot unread messages and the number of messages available in the
     * specified mailbox.
     *
     * @param FolderKey $folderKey
     *
     * @return array
     *
     * @throws MailClientException if any exception occurs
     */
    public function getMessageCount(FolderKey $folderKey): array;


    /**
     * Appends new attachments to the specified message..
     *
     * @param MessageKey $messageKey
     * @param FileAttachmentList $attachmentList
     *
     * @return FileAttachmentList the created FileAttachmentItemList
     *
     * @throws MailClientException if any exception occurs
     */
    public function createAttachments(
        MessageKey $messageKey,
        FileAttachmentList $attachmentList
    ): FileAttachmentList;


    /**
     * Returns the FileAttachments in an FileAttachmentList for the specified message.
     *
     * @param MessageKey $messageKey
     *
     * @return FileAttachmentList
     *
     * @throws MailClientException if any exception occurs
     */
    public function getFileAttachmentList(MessageKey $messageKey): FileAttachmentList;


    /**
     * Deletes the Attachment represented by the AttachmentKey for the specified message.
     *
     * @param AttachmentKey $attachmentKey
     *
     * @return MessageKey The key of the Message for which the attachment was deleted. If removing
     * the attachment triggered a location change of the message, this must be reflected in the returned
     * key.
     *
     * @throws MailClientException if any exception occurs
     */
    public function deleteAttachment(AttachmentKey $attachmentKey): MessageKey;


    /**
     * Sets the flags specified in FlagList for the message represented by MessageKey.
     * Existing flags will not be removed if they do not appear in the $flagList.
     *
     * @param MessageKey $messageKey
     * @param FlagList $flagList
     *
     * @return bool if the operation succeeded, otherwise false
     *
     * @throws MailClientException if any exception occurs
     */
    public function setFlags(MessageKey $messageKey, FlagList $flagList): bool;


    /**
     * Moves the message identified by $messageKey to the folder specified with $folderKey.
     * Does nothing if both mailFolderIds are the same.
     *
     * @param MessageKey $messageKey
     * @param FolderKey $folderKey
     *
     * @return MessageKey The new MessageKey for the moved Message
     *
     * @throws MailClientException if the MailAccount-id found in $messageKey and $folderKey are
     * not the same, or if any other error occurs
     */
    public function moveMessage(MessageKey $messageKey, FolderKey $folderKey): MessageKey;
}
