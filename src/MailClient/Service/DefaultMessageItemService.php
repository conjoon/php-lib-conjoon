<?php

/**
 * conjoon
 * php-cn_imapuser
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-cn_imapuser
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

use Conjoon\Core\Data\MimeType;
use Conjoon\Core\Data\ParameterBag;
use Conjoon\Core\Data\SortInfoList;
use Conjoon\Filter\Filter;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Resource\MessageBodyOptions;
use Conjoon\MailClient\Resource\MessageBodyQuery;
use Conjoon\MailClient\MailClient;
use Conjoon\MailClient\Exception\MailClientException;
use Conjoon\MailClient\Message\AbstractMessageItem;
use Conjoon\MailClient\Message\Flag\FlagList;
use Conjoon\MailClient\Message\MessageBody;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\MailClient\Message\MessageItemList;
use Conjoon\MailClient\Message\MessagePart;
use Conjoon\MailClient\Message\Text\MessageItemFieldsProcessor;
use Conjoon\MailClient\Message\Text\PreviewTextProcessor;
use Conjoon\MailClient\Reader\ReadableMessagePartContentProcessor;
use Conjoon\MailClient\Resource\MessageItemListQuery;
use Conjoon\MailClient\Resource\MessageItemQuery;
use Conjoon\MailClient\Writer\WritableMessagePartContentProcessor;
use Conjoon\Core\Data\ArrayUtil;
use Conjoon\Math\Expression\FunctionalExpression;
use Conjoon\Math\Value;
use Conjoon\Math\VariableName;

/**
 * Class DefaultMessageItemService.
 * Default implementation of a MessageItemService.
 *
 * @package App\Imap\Service
 */
class DefaultMessageItemService implements MessageItemService
{
    /**
     * @var MailClient
     */
    protected MailClient $mailClient;

    /**
     * @var PreviewTextProcessor
     */
    protected PreviewTextProcessor $previewTextProcessor;

    /**
     * @var ReadableMessagePartContentProcessor
     */
    protected ReadableMessagePartContentProcessor $readableMessagePartContentProcessor;

    /**
     * @var WritableMessagePartContentProcessor
     */
    protected WritableMessagePartContentProcessor $writableMessagePartContentProcessor;

    /**
     * @var MessageItemFieldsProcessor
     */
    protected MessageItemFieldsProcessor $messageItemFieldsProcessor;


    /**
     * DefaultMessageItemService constructor.
     *
     * @param MailClient $mailClient
     * @param MessageItemFieldsProcessor $messageItemFieldsProcessor
     * @param ReadableMessagePartContentProcessor $readableMessagePartContentProcessor
     * @param WritableMessagePartContentProcessor $writableMessagePartContentProcessor
     * @param PreviewTextProcessor $previewTextProcessor
     */
    public function __construct(
        MailClient $mailClient,
        MessageItemFieldsProcessor $messageItemFieldsProcessor,
        ReadableMessagePartContentProcessor $readableMessagePartContentProcessor,
        WritableMessagePartContentProcessor $writableMessagePartContentProcessor,
        PreviewTextProcessor $previewTextProcessor
    ) {
        $this->messageItemFieldsProcessor = $messageItemFieldsProcessor;
        $this->mailClient = $mailClient;
        $this->readableMessagePartContentProcessor = $readableMessagePartContentProcessor;
        $this->writableMessagePartContentProcessor = $writableMessagePartContentProcessor;
        $this->previewTextProcessor = $previewTextProcessor;
    }


// -------------------------
//  MessageItemService
// -------------------------
    /**
     * @inheritdoc
     */
    public function getMailClient(): MailClient
    {
        return $this->mailClient;
    }


    /**
     * @inheritdoc
     */
    public function getMessageItemFieldsProcessor(): MessageItemFieldsProcessor
    {
        return $this->messageItemFieldsProcessor;
    }


    /**
     * @inheritdoc
     */
    public function getPreviewTextProcessor(): PreviewTextProcessor
    {
        return $this->previewTextProcessor;
    }


    /**
     * @inheritdoc
     */
    public function getReadableMessagePartContentProcessor(): ReadableMessagePartContentProcessor
    {
        return $this->readableMessagePartContentProcessor;
    }


    /**
     * @inheritdoc
     */
    public function getWritableMessagePartContentProcessor(): WritableMessagePartContentProcessor
    {
        return $this->writableMessagePartContentProcessor;
    }


    /**
     * @inheritdoc
     */
    public function getMessageItemList(FolderKey $folderKey, MessageItemListQuery $query): MessageItemList
    {
        $messageItemList = $this->mailClient->getMessageItemList(
            $folderKey,
            $query
        );

        foreach ($messageItemList as $listMessageItem) {
            $this->charsetConvertHeaderFields($listMessageItem);
        }

        return $messageItemList;
    }


    /**
     * @inheritdoc
     */
    public function getMessageItem(MessageKey $messageKey, MessageItemQuery $query): MessageItem
    {
        $messageItem = $this->mailClient->getMessageItem($messageKey, $query);
        $this->charsetConvertHeaderFields($messageItem);
        return $messageItem;
    }


    /**
     * @inheritdoc
     */
    public function deleteMessage(MessageKey $messageKey): bool
    {
        $result = false;

        try {
            $result = $this->mailClient->deleteMessage($messageKey);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function getMessageItemDraft(MessageKey $messageKey, MessageItemQuery $query): ?MessageItemDraft
    {
        $messageItemDraft = $this->mailClient->getMessageItemDraft($messageKey, $query);
        $this->charsetConvertHeaderFields($messageItemDraft);
        return $messageItemDraft;
    }


    /**
     * @inheritdoc
     */
    public function sendMessageDraft(MessageKey $messageKey): bool
    {

        $result = false;

        try {
            $result = $this->mailClient->sendMessageDraft($messageKey);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function getMessageBody(MessageKey $messageKey): MessageBody
    {
        $messageBody = $this->mailClient->getMessageBody($messageKey);
        $this->processMessageBody($messageBody);
        return $messageBody;
    }


    /**
     * @inheritdoc
     */
    public function getUnreadMessageCount(FolderKey $folderKey): int
    {
        return $this->getMailClient()->getMessageCount($folderKey)["unreadMessages"];
    }


    /**
     * @inheritdoc
     */
    public function getTotalMessageCount(FolderKey $folderKey): int
    {
        return $this->getMailClient()->getMessageCount($folderKey)["totalMessages"];
    }

    /**
     * @inheritdoc
     */
    public function setFlags(MessageKey $messageKey, FlagList $flagList): bool
    {
        return $this->getMailClient()->setFlags($messageKey, $flagList);
    }


    /**
     * @inheritdoc
     */
    public function moveMessage(MessageKey $messageKey, FolderKey $folderKey): ?MessageKey
    {

        try {
            return $this->getMailClient()->moveMessage($messageKey, $folderKey);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return null;
    }


    /**
     * @inheritdoc
     */
    public function createMessageDraft(FolderKey $folderKey, MessageItemDraft $draft): ?MessageItemDraft
    {

        if ($draft->getMessageKey()) {
            throw new ServiceException(
                "Cannot create a MessageItemDraft that has a MessageKey"
            );
        }

        return $this->getMailClient()->createMessageDraft($folderKey, $draft);
    }


    /**
     * @inheritdoc
     */
    public function createMessageBodyDraft(FolderKey $folderKey, MessageBodyDraft $draft): ?MessageBodyDraft
    {

        if ($draft->getMessageKey()) {
            throw new ServiceException(
                "Cannot create a MessageBodyDraft that has a MessageKey"
            );
        }

        $this->processMessageBodyDraft($draft);

        try {
            return $this->getMailClient()->createMessageBodyDraft($folderKey, $draft);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return null;
    }


    /**
     * @inheritdoc
     */
    public function updateMessageBodyDraft(MessageBodyDraft $draft): ?MessageBodyDraft
    {

        if (!$draft->getMessageKey()) {
            throw new ServiceException(
                "Cannot update a MessageBodyDraft that has no MessageKey"
            );
        }

        $this->processMessageBodyDraft($draft);

        try {
            return $this->getMailClient()->updateMessageBodyDraft($draft);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return null;
    }


    /**
     * @inheritdoc
     */
    public function updateMessageDraft(MessageItemDraft $messageItemDraft): ?MessageItemDraft
    {

        $updated = null;

        try {
            $updated = $this->getMailClient()->updateMessageDraft($messageItemDraft);
        } catch (MailClientException $e) {
            // intentionally left empty
        }

        return $updated;
    }


// -----------------------
// Helper
// -----------------------

    /**
     * Makes sure that there is a text/plain part for this message if only text/html was
     * available. If only text/plain is available, a text/html part will be created.
     *
     * @param MessageBodyDraft $messageBodyDraft
     *
     * @return MessageBodyDraft
     */
    protected function processMessageBodyDraft(MessageBodyDraft $messageBodyDraft): MessageBodyDraft
    {

        $targetCharset = "UTF-8";
        $plainPart = $messageBodyDraft->getTextPlain();
        $htmlPart = $messageBodyDraft->getTextHtml();

        if (!$plainPart && $htmlPart) {
            $plainPart = new MessagePart(
                $htmlPart->getContents(),
                $htmlPart->getCharset(),
                MimeType::TEXT_PLAIN
            );
            $messageBodyDraft->setTextPlain($plainPart);
        }
        if ($plainPart && !$htmlPart) {
            $htmlPart = new MessagePart(
                $plainPart->getContents(),
                $plainPart->getCharset(),
                MimeType::TEXT_HTML
            );
            $messageBodyDraft->setTextHtml($htmlPart);
        }

        if (!$plainPart && !$htmlPart) {
            $plainPart = new MessagePart("", $targetCharset, MimeType::TEXT_PLAIN);
            $messageBodyDraft->setTextPlain($plainPart);
            $htmlPart = new MessagePart("", $targetCharset, MimeType::TEXT_HTML);
            $messageBodyDraft->setTextHtml($htmlPart);
        }

        // wee need to strip any tags
        $this->getWritableMessagePartContentProcessor()->process($plainPart, $targetCharset);

        // we need to convert line breaks to html tags
        $this->getWritableMessagePartContentProcessor()->process($htmlPart, $targetCharset);

        return $messageBodyDraft;
    }


    /**
     * Processes the specified MessageItem with the help of this MessageItemFieldsProcessor
     * @param AbstractMessageItem $messageItem
     * @return AbstractMessageItem
     */
    protected function charsetConvertHeaderFields(AbstractMessageItem $messageItem): AbstractMessageItem
    {

        $targetCharset = "UTF-8";

        return $this->getMessageItemFieldsProcessor()->process($messageItem, $targetCharset);
    }


    /**
     * Processes the contents of the MessageBody's Parts and makes sure this converter converts
     * the contents to proper UTF-8.
     * Additionally, the text/html part will be filtered by this $htmlReadableStrategy.
     *
     *
     * @param MessageBody $messageBody
     *
     * @return MessageBody
     *
     * @see MessagePartContentProcessor::process
     */
    protected function processMessageBody(MessageBody $messageBody): MessageBody
    {

        $textPlainPart = $messageBody->getTextPlain();
        $textHtmlPart = $messageBody->getTextHtml();

        $targetCharset = "UTF-8";

        if ($textPlainPart) {
            $this->getReadableMessagePartContentProcessor()->process($textPlainPart, $targetCharset);
        }

        if ($textHtmlPart) {
            $this->getReadableMessagePartContentProcessor()->process($textHtmlPart, $targetCharset);
        }

        return $messageBody;
    }


    /**
     * Processes the specified MessagePart and returns its contents properly converted to UTF-8
     * and stripped of all HTML-tags.
     * Length property will be extracted from the $options.
     *
     * @param MessagePart $messagePart The message part that should be looked up
     * @param MessageBodyOptions|null $options The options available for processing the MessagePart
     *
     * @return MessagePart|null
     *
     * @see PreviewTextProcessor::process
     */
    protected function processTextForPreview(
        MessagePart $messagePart,
        ?MessageBodyOptions $options
    ): MessagePart {

        $mimeType = $messagePart->getMimeType();

        $opts    = [];

        $trimApi = false;
        $length  = null;

        if ($options) {
            $length = $options->getLength($mimeType);
            $trimApi = $options->getTrimApi($mimeType);
        }

        if ($trimApi && $length) {
            $opts["length"] = $length;
        }

        return $this->getPreviewTextProcessor()->process($messagePart, "UTF-8", $opts);
    }
}
