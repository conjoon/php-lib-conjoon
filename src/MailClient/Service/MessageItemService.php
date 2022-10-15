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

namespace Conjoon\MailClient\Service;

use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\Resource\MessageItemQuery;
use Conjoon\MailClient\Exception\MailClientException;
use Conjoon\MailClient\Exception\MailFolderNotFoundException;
use Conjoon\MailClient\MailClient;
use Conjoon\MailClient\Message\Flag\FlagList;
use Conjoon\MailClient\Message\ListMessageItem;
use Conjoon\MailClient\Message\MessageBody;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\MailClient\Message\MessageItemList;
use Conjoon\MailClient\Message\Text\MessageItemFieldsProcessor;
use Conjoon\MailClient\Message\Text\PreviewTextProcessor;
use Conjoon\MailClient\Data\Reader\ReadableMessagePartContentProcessor;
use Conjoon\MailClient\Data\Resource\MessageItemListQuery;
use Conjoon\MailClient\Data\Writer\WritableMessagePartContentProcessor;

/**
 * Interface MessageItemService.
 *
 * Provides a contract for MessageItem(List)/MessageBody related operations.
 */
interface MessageItemService
{
    /**
     * Returns a MessageItemList containing the MessageItems for the
     * specified MailAccount and the MailFolder.
     *
     * @param FolderKey $folderKey
     * @param MessageItemListQuery $query The resource query for the
     * MessageItemLi
     *
     * @return MessageItemList
     *
     * @throws MailFolderNotFoundException|MailClientException if the mail folder
     * was not found, or a generic MailClientException indicating an error while
     * communicating with the underlying (mail)backend.
     */
    public function getMessageItemList(FolderKey $folderKey, MessageItemListQuery $query): MessageItemList;


    /**
     * Returns a single MessageItem.
     *
     * @param MessageKey $messageKey
     * @param MessageItemQuery $query
     *
     * @return MessageItem
     */
    public function getMessageItem(MessageKey $messageKey, MessageItemQuery $query): MessageItem;


    /**
     * Deletes a single Message permanently.
     *
     * @param MessageKey $messageKey
     * @return bool true if successful, otherwise false
     */
    public function deleteMessage(MessageKey $messageKey): bool;


    /**
     * Returns a single MessageItemDraft.
     *
     * @param MessageKey $messageKey
     * @param MessageItemQuery $query
     *
     * @return MessageItemDraft|null or null if no entity for the key was found
     */
    public function getMessageItemDraft(MessageKey $messageKey, MessageItemQuery $query): ?MessageItemDraft;


    /**
     * Returns a single MessageBody.
     *
     * @param MessageKey $messageKey
     * @return MessageBody
     */
    public function getMessageBody(MessageKey $messageKey): MessageBody;


    /**
     * Creates a single MessageItemDraft and returns it along with the generated MessageKey.
     * Returns null if the MessageItemDraft could not be created.
     * The created message will be marked as a draft.
     *
     * @param FolderKey $folderKey
     * @param MessageItemDraft $draft The draft to create
     *
     * @return MessageItemDraft|null
     *
     * @throws ServiceException|MailFolderNotFoundException|MailClientException if the draft cannot be used
     * with this service, if the mail folder was not found, or a generic MailClientException indicating an error while
     * communicating with the underlying (mail)backend.
     */
    public function createMessageDraft(FolderKey $folderKey, MessageItemDraft $draft): ?MessageItemDraft;


    /**
     * Creates a single MessageBodyDraft and returns it along with the generated MessageKey.
     * Returns null if the MessageBodyDraft could not be created.
     * The created message will be marked as a draft.
     *
     * @param FolderKey $folderKey
     * @param MessageBodyDraft $draft The draft to create
     *
     * @return MessageBodyDraft|null
     *
     * @throws ServiceException if $draft already has a MessageKey
     */
    public function createMessageBodyDraft(FolderKey $folderKey, MessageBodyDraft $draft): ?MessageBodyDraft;


    /**
     * Updates the MessageBodyDraft with the data.
     * Implementing APIs should be aware of different protocol support and that some server implementations (IMAP)
     * need to create an entirely new Message if data needs to be adjusted, so the MessageKey  of the returned
     * MessageItemDraft might not equal to the MessageKey in $messageItemDraft.
     * The MessageBodyDraft will explicitly get flagged as a "draft".
     *
     * @param MessageBodyDraft $draft The draft to create
     *
     * @return MessageBodyDraft|null
     *
     * @throws ServiceException if $draft has no messageKey
     */
    public function updateMessageBodyDraft(MessageBodyDraft $draft): ?MessageBodyDraft;


    /**
     * Updated the Message with the specified MessageItemDraft (if the message is flagged as "draft") and returns the
     * updated MessageItemDraft.
     * Implementing APIs should be aware of different protocol support and that some server implementations (IMAP)
     * need to create an entirely new Message if data needs to be adjusted, so the MessageKey  of the returned
     * MessageItemDraft might not equal to the MessageKey in $messageItemDraft.
     * The MessageBodyDraft will explicitly get flagged as a "draft".
     *
     * @param MessageItemDraft $messageItemDraft
     *
     * @return MessageItemDraft|null
     */
    public function updateMessageDraft(MessageItemDraft $messageItemDraft): ?MessageItemDraft;


    /**
     * Sends the MessageItemDraft with the specified $messageKey. The message will not
     * be send if it is not a DRAFT message.
     *
     * @param MessageKey $messageKey
     * @return bool true if sending was successfully, otherwise false
     */
    public function sendMessageDraft(MessageKey $messageKey): bool;


    /**
     * Returns the total number of messages in the specified $mailFolderId for the specified $account;
     *
     * @param FolderKey $folderKey
     * @return int
     */
    public function getTotalMessageCount(FolderKey $folderKey): int;


    /**
     * Returns the total number of UNREAD messages in the specified $mailFolderId for the specified $account;
     *
     * @param FolderKey $folderKey
     * @return int
     */
    public function getUnreadMessageCount(FolderKey $folderKey): int;


    /**
     * Sets the flags in $flagList for the Message identified with MessageKey.
     *
     * @param MessageKey $messageKey
     * @param FlagList $flagList
     *
     * @return boolean
     */
    public function setFlags(MessageKey $messageKey, FlagList $flagList): bool;

    /**
     * Moves the message identified by $messageKey to the destination folder specified with
     * $folderKey. Returns null if the operation failed.
     *
     * @param MessageKey $messageKey
     * @param FolderKey $folderKey
     *
     * @return null|MessageKey
     */
    public function moveMessage(MessageKey $messageKey, FolderKey $folderKey): ?MessageKey;


    /**
     * Returns the MessageItemFieldsProcessor used by this MessageService.
     *
     * @return MessageItemFieldsProcessor
     */
    public function getMessageItemFieldsProcessor(): MessageItemFieldsProcessor;


    /**
     * Returns the ReadableMessagePartContentProcessor used by this MessageService.
     *
     * @return ReadableMessagePartContentProcessor
     */
    public function getReadableMessagePartContentProcessor(): ReadableMessagePartContentProcessor;


    /**
     * Returns the WritableMessagePartContentProcessor used by this MessageService.
     *
     * @return WritableMessagePartContentProcessor
     */
    public function getWritableMessagePartContentProcessor(): WritableMessagePartContentProcessor;


    /**
     * Returns the PreviewTextProcessor used by this MessageService.
     *
     * @return PreviewTextProcessor
     */
    public function getPreviewTextProcessor(): PreviewTextProcessor;


    /**
     * Returns the MailClient used by this MessageService.
     *
     * @return MailClient
     */
    public function getMailClient(): MailClient;
}
