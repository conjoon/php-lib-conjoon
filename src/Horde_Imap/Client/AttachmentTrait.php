<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */


declare(strict_types=1);

namespace Conjoon\Horde_Imap\Client;

use Conjoon\MailClient\Exception\MailClientException;
use Conjoon\MailClient\Message\Attachment\FileAttachment;
use Conjoon\MailClient\Message\Attachment\FileAttachmentList;
use Conjoon\MailClient\Data\CompoundKey\AttachmentKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Message\Flag\DraftFlag;
use Conjoon\MailClient\Message\Flag\FlagList;
use Exception;
use Horde_Mime_Exception;
use Horde_Mime_Headers;
use Horde_Mime_Part;
use RuntimeException;

/**
 * Helper-Trait for attachment operations
 *
 * Trait AttachmentTrait
 * @package Conjoon\Horde_Imap\Client
 */
trait AttachmentTrait
{
    /**
     * Builds an attachment from the specified data.
     *
     * @param MessageKey $key
     * @param Horde_Mime_Part $part
     * @param string $fileName
     * @return FileAttachment
     */
    protected function buildAttachment(
        MessageKey $key,
        Horde_Mime_Part $part,
        string $fileName
    ): FileAttachment {

        $mimeType = $part->getType();

        $attachment = new FileAttachment([
            "type" => $mimeType,
            "text" => $fileName,
            "size" => $part->getBytes(),
            "content" =>  base64_encode($part->getContents()),
            "encoding" => "base64"
        ]);

        return $attachment->setAttachmentKey(new AttachmentKey($key, $this->generateAttachmentId($attachment)));
    }


    /**
     * @inheritdoc
     */
    public function createAttachments(MessageKey $messageKey, FileAttachmentList $attachments): FileAttachmentList
    {
        try {
            $mailFolderId = $messageKey->getMailFolderId();
            $mailAccountId = $messageKey->getMailAccountId();
            $client = $this->connect($messageKey);

            $target = $this->getFullMsg($messageKey, $client);

            $rawMessage = $this->getAttachmentComposer()->compose($target, $attachments);

            $ids = $client->append($mailFolderId, [["data" => $rawMessage]]);
            $newMessageKey = new MessageKey($mailAccountId, $mailFolderId, (string)$ids->ids[0]);
            $newList = new FileAttachmentList();
            foreach ($attachments as $attachment) {
                $newList[] = $attachment->setAttachmentKey(
                    new AttachmentKey($newMessageKey, $this->generateAttachmentId($attachment))
                );
            }

            $flagList = new FlagList();
            $flagList[] = new DraftFlag(true);
            $this->setFlags($newMessageKey, $flagList);

            // delete the previous draft
            $this->deleteMessage($messageKey);

            return $newList;
        } catch (Exception $e) {
            throw new MailClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function getFileAttachmentList(MessageKey $messageKey): FileAttachmentList
    {
        try {
            $client = $this->connect($messageKey);
            $target = $this->getFullMsg($messageKey, $client);
            $message = $this->parseMessage($target);

            $attachmentList = new FileAttachmentList();

            foreach ($message as $part) {
                $isAttachment = $part->isAttachment();
                $name = $part->getName();

                if ($isAttachment && !!$name) {
                    $attachmentList[] = $this->buildAttachment(
                        $messageKey,
                        $part,
                        $name
                    );
                }
            }

            return $attachmentList;
        } catch (Exception $e) {
            throw new MailClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function deleteAttachment(AttachmentKey $attachmentKey): MessageKey
    {
        try {
            $messageKey = $attachmentKey->getMessageKey();
            $mailFolderId = $attachmentKey->getMailFolderId();
            $mailAccountId = $attachmentKey->getMailAccountId();
            $client = $this->connect($messageKey);

            $target = $this->getFullMsg($messageKey, $client);

            $message = $this->parseMessage($target);
            $headers = $this->parseHeaders($target);

            $basePart = $this->createBasePart();


            foreach ($message as $part) {
                if (
                    $part->isAttachment() &&
                    !!$part->getName() &&
                    $this->buildAttachment(
                        $messageKey,
                        $part,
                        $part->getName()
                    )->getAttachmentKey()->equals($attachmentKey)
                ) {
                    continue;
                }
                $basePart[] = $part;
            }

            $headers = $basePart->addMimeHeaders(["headers" => $headers]);

            $rawMessage = trim($headers->toString()) .
                "\n\n" .
                trim($basePart->toString());


            $ids = $client->append($mailFolderId, [["data" => $rawMessage]]);
            $newMessageKey = new MessageKey($mailAccountId, $mailFolderId, (string)$ids->ids[0]);

            $flagList = new FlagList();
            $flagList[] = new DraftFlag(true);
            $this->setFlags($newMessageKey, $flagList);

            // delete the previous draft
            $this->deleteMessage($messageKey);

            return $newMessageKey;
        } catch (Exception $e) {
            throw new MailClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * Computes the id that should be used for the $attachment.
     * This method is guaranteed to return one and the same id based on the base64-encoded content of
     * the attachment and the "text" ot the attachment.
     * @param FileAttachment $attachment
     *
     * @return string
     *
     * @throws RuntimeException if the encoding of the attachment is not base64
     */
    protected function generateAttachmentId(FileAttachment $attachment): string
    {
        if ($attachment->getEncoding() !== "base64") {
            throw new RuntimeException("encoding must be \"base64\"");
        }

        return md5($attachment->getText() . $attachment->getContent());
    }


    /**
     * Returns the parsed message.
     *
     * @param string $target
     *
     * @return Horde_Mime_Part
     *
     * @throws Horde_Mime_Exception
     */
    protected function parseMessage(string $target): Horde_Mime_Part
    {
        return Horde_Mime_Part::parseMessage($target);
    }


    /**
     * Returns the parsed headers.
     *
     * @param string $target
     *
     * @return Horde_Mime_Headers
     *
     */
    protected function parseHeaders(string $target): Horde_Mime_Headers
    {
        return Horde_Mime_Headers::parseHeaders($target);
    }


    /**
     * Returns a base part for a message.
     *
     * @return Horde_Mime_Part
     */
    protected function createBasePart(): Horde_Mime_Part
    {
        $basePart = new Horde_Mime_Part();
        $basePart->setType('multipart/mixed');
        $basePart->isBasePart(true);

        return $basePart;
    }
}
