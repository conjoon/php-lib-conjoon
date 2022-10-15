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

namespace Conjoon\Horde_Imap\Client;

use Conjoon\Core\Data\MimeType;
use Conjoon\Core\Data\SortInfoList;
use Conjoon\Filter\Filter;
use Conjoon\MailClient\Data\CompoundKey\CompoundKey;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Data\MailAddress;
use Conjoon\MailClient\Data\MailAddressList;
use Conjoon\MailClient\Exception\MailFolderNotFoundException;
use Conjoon\MailClient\Folder\ListMailFolder;
use Conjoon\MailClient\Folder\MailFolderList;
use Conjoon\MailClient\Imap\ImapClientException;
use Conjoon\MailClient\MailClient;
use Conjoon\MailClient\Message\Composer\AttachmentComposer;
use Conjoon\MailClient\Message\Composer\BodyComposer;
use Conjoon\MailClient\Message\Composer\HeaderComposer;
use Conjoon\MailClient\Message\Flag\AnsweredFlag;
use Conjoon\MailClient\Message\Flag\DraftFlag;
use Conjoon\MailClient\Message\Flag\FlagList;
use Conjoon\MailClient\Message\MessageBody;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\MailClient\Message\MessageItemList;
use Conjoon\MailClient\Message\MessagePart;
use Conjoon\MailClient\Data\Resource\MailFolderListQuery;
use Conjoon\MailClient\Data\Resource\MessageItemListQuery;
use Conjoon\MailClient\Data\Resource\MessageItemQuery;
use DateTime;
use Exception;
use Horde_Imap_Client;
use Horde_Imap_Client_Exception;
use Horde_Imap_Client_Exception_NoSupportExtension;
use Horde_Imap_Client_Fetch_Query;
use Horde_Imap_Client_Ids;
use Horde_Imap_Client_Socket;
use Horde_Mail_Transport;
use Horde_Mail_Transport_Smtphorde;
use Horde_Mime_Headers;
use Horde_Mime_Mail;
use Horde_Mime_Part;

/**
 * Class HordeClient.
 * Default implementation of a HordeClient, using \Horde_Imap_Client to communicate with
 * Imap Mail Servers.
 *
 * @package Conjoon\MailClient\Imap
 */
class HordeClient implements MailClient
{
    use FilterTrait;
    use AttachmentTrait;

    /**
     * @var MailAccount
     */
    protected MailAccount $mailAccount;

    /**
     * @var Horde_Imap_Client_Socket|null
     */
    protected ?Horde_Imap_Client_Socket $socket = null;

    /**
     * @var Horde_Mail_Transport|null
     */
    protected ?Horde_Mail_Transport $mailer = null;

    /**
     * @var BodyComposer
     */
    protected BodyComposer $bodyComposer;

    /**
     * @var HeaderComposer
     */
    protected HeaderComposer $headerComposer;

    /**
     * @var AttachmentComposer
     */
    protected AttachmentComposer $attachmentComposer;

    /**
     * @var SortInfoStrategy
     */
    protected SortInfoStrategy $sortInfoStrategy;


    /**
     * HordeClient constructor.
     *
     * @param MailAccount $account
     * @param BodyComposer $bodyComposer
     * @param HeaderComposer $headerComposer
     * @param AttachmentComposer $attachmentComposer
     * @param SortInfoStrategy $sortInfoStrategy
     */
    public function __construct(
        MailAccount $account,
        BodyComposer $bodyComposer,
        HeaderComposer $headerComposer,
        AttachmentComposer $attachmentComposer,
        SortInfoStrategy $sortInfoStrategy
    ) {
        $this->mailAccount = $account;
        $this->bodyComposer = $bodyComposer;
        $this->headerComposer = $headerComposer;
        $this->attachmentComposer = $attachmentComposer;
        $this->sortInfoStrategy = $sortInfoStrategy;
    }

    /**
     * Returns the SortInfoStrategy this instance was configured with.
     *
     * @return SortInfoStrategy
     */
    public function getSortInfoStrategy(): SortInfoStrategy
    {
        return $this->sortInfoStrategy;
    }



    /**
     * Returns the BodyComposer this instance was configured with.
     *
     * @return BodyComposer
     */
    public function getBodyComposer(): BodyComposer
    {
        return $this->bodyComposer;
    }


    /**
     * Returns the HeaderComposer this instance was configured with.
     *
     * @return HeaderComposer
     */
    public function getHeaderComposer(): HeaderComposer
    {
        return $this->headerComposer;
    }


    /**
     * Returns the AttachmentComposer this instance was configured with.
     *
     * @return AttachmentComposer
     */
    public function getAttachmentComposer(): AttachmentComposer
    {
        return $this->attachmentComposer;
    }

    /**
     * Returns the MailAccount providing connection info for the CompoundKey
     * or string (which will be treated as the id of the MailAccount to look up).
     *
     * @param string|CompoundKey $key
     *
     * @return MailAccount|null
     */
    public function getMailAccount(CompoundKey|string $key): ?MailAccount
    {

        $id = $key;

        if ($key instanceof CompoundKey) {
            $id = $key->getMailAccountId();
        }

        if ($this->mailAccount->getId() !== $id) {
            return null;
        }

        return $this->mailAccount;
    }


    /**
     * Creates a \Horde_Imap_Client_Socket.
     * Looks up the MailAccount used by this instance and throws an Exception
     * if the passed CompoundKey/id does not share the same mailAccountId/value
     * with the id of "this" MailAccount.
     * Returns a \Horde_Imap_Client_Socket if connecting was successful.
     *
     * @param CompoundKey|string $key
     *
     * @return Horde_Imap_Client_Socket
     *
     * @throws ImapClientException if the MailAccount used with this Client does not share
     * the same mailAccountId with the $key
     */
    public function connect(CompoundKey|string $key): Horde_Imap_Client_Socket
    {

        if (!$this->socket) {
            $account = $this->getMailAccount($key);

            if (!$account) {
                throw new ImapClientException(
                    "The passed \"key\" does not share the same id-value with " .
                    "the MailAccount this class was configured with."
                );
            }

            $this->socket = new Horde_Imap_Client_Socket(array(
                'username' => $account->getInboxUser(),
                'password' => $account->getInboxPassword(),
                'hostspec' => $account->getInboxAddress(),
                'port' => $account->getInboxPort(),
                'secure' => $account->getInboxSsl() ? 'ssl' : null
            ));
        }

        return $this->socket;
    }

// --------------------------
//   MailClient- Interface
// --------------------------

    /**
     * @inheritdoc
     */
    public function getMailFolderList(MailAccount $mailAccount, MailFolderListQuery $query): MailFolderList
    {
        $fields = $query->getFields();

        try {
            $client = $this->connect($mailAccount->getId());

            $mailboxes = $client->listMailboxes(
                "*",
                Horde_Imap_Client::MBOX_ALL,
                ["attributes" => true]
            );

            $mailFolderList = new MailFolderList();

            foreach ($mailboxes as $folderId => $mailbox) {
                $args = ["name" => $folderId];

                if ($this->isMailboxSelectable($mailbox)) {
                    foreach (
                        [
                        "unreadMessages" => Horde_Imap_Client::STATUS_UNSEEN,
                        "totalMessages" => Horde_Imap_Client::STATUS_MESSAGES
                        ] as $field => $attribute
                    ) {
                        if (in_array($field, $fields)) {
                            $args[$field] = $attribute;
                        }
                    }

                    if (count($args) > 1) {
                        $status = $client->status(...array_values($args));

                        foreach (["unseen" => "unreadMessages", "messages" => "totalMessages"] as $key => $val) {
                            if (array_key_exists($key, $status)) {
                                $args[$val] = $status[$key];
                            } else {
                                unset($args[$val]);
                            }
                        }
                    }
                }

                $properties = [
                    "delimiter" => $mailbox["delimiter"],
                    "attributes" => $mailbox["attributes"]
                ];

                foreach ($args as $field => $attribute) {
                    $properties = in_array($field, $fields)
                        ? array_merge($properties, [$field => $attribute])
                        : $properties;
                }

                $folderKey = new FolderKey($mailAccount, $folderId);
                $mailFolderList[] = new ListMailFolder($folderKey, $properties);
            }
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }

        return $mailFolderList;
    }


    /**
     * @inheritdoc
     */
    public function getMessageItemList(FolderKey $folderKey, MessageItemListQuery $query): MessageItemList
    {
        try {
            $client = $this->connect($folderKey);

            if (!$this->doesMailboxExist($folderKey)) {
                throw new MailFolderNotFoundException(
                    "The mailbox \"{$folderKey->getId()}\" was not found for this account."
                );
            }

            $results = $this->queryItems(
                $client,
                $folderKey,
                $query->getSort(),
                $query->getFilter()
            );
            $fetchedItems = $this->fetchMessageItems(
                $client,
                $results["match"],
                $folderKey->getId(),
                $query
            );


            return $this->buildMessageItems(
                $client,
                $folderKey,
                $fetchedItems,
                $query->getFields()
            );
        } catch (Horde_Imap_Client_Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function getMessageCount(FolderKey $folderKey): array
    {

        try {
            $client = $this->connect($folderKey);
            $status = $client->status(
                $folderKey->getId(),
                Horde_Imap_Client::STATUS_UNSEEN,
                Horde_Imap_Client::STATUS_MESSAGES,
            );

            return [
                "unreadMessages" => $status["unseen"],
                "totalMessages" => $status["messages"]
            ];
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function getMessageItem(MessageKey $messageKey, MessageItemQuery $query): ?MessageItem
    {
        return $this->getItemOrDraft($messageKey, $query, true);
    }


    /**
     * @inheritdoc
     */
    public function getMessageItemDraft(MessageKey $messageKey, MessageItemQuery $query): ?MessageItemDraft
    {
        return $this->getItemOrDraft($messageKey, $query, false);
    }


    /**
     * @inheritdoc
     */
    public function deleteMessage(MessageKey $messageKey): bool
    {

        try {
            $mailFolderId = $messageKey->getMailFolderId();
            $id = $messageKey->getId();

            $client = $this->connect($messageKey);

            $rangeList = new Horde_Imap_Client_Ids();
            $rangeList->add($id);

            $idList = $client->expunge($mailFolderId, ["delete" => true, "ids" => $rangeList, "list" => true]);

            if (count($idList) === 0) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function getMessageBody(MessageKey $messageKey): MessageBody
    {

        $mailFolderId = $messageKey->getMailFolderId();
        $messageItemId = $messageKey->getId();

        try {
            $client = $this->connect($messageKey);

            $query = new Horde_Imap_Client_Fetch_Query();
            $query->structure();


            $uid = new Horde_Imap_Client_Ids($messageItemId);

            $list = $client->fetch($mailFolderId, $query, array(
                'ids' => $uid
            ));

            $serverItem = $list->first();

            $messageStructure = $serverItem->getStructure();

            $d = $this->getContents($client, $messageStructure, $messageKey, [
                "plain", "html"
            ]);

            $body = new MessageBody($messageKey);

            if ($d["html"]["content"]) {
                $htmlPart = new MessagePart($d["html"]["content"], $d["html"]["charset"], MimeType::TEXT_HTML);
                $body->setTextHtml($htmlPart);
            }

            $plainPart = new MessagePart($d["plain"]["content"], $d["plain"]["charset"], MimeType::TEXT_PLAIN);
            $body->setTextPlain($plainPart);
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }


        return $body;
    }


    /**
     * @inheritdoc
     */
    public function setFlags(MessageKey $messageKey, FlagList $flagList): bool
    {
        try {
            $client = $this->connect($messageKey);

            $messageItemId = $messageKey->getId();
            $mailFolderId = $messageKey->getMailFolderId();

            $ids = new Horde_Imap_Client_Ids([$messageItemId]);

            $options = [
                'ids' => $ids
            ];

            foreach ($flagList as $flag) {
                $type = $flag->getValue() ? "add" : "remove";

                if (!isset($options[$type])) {
                    $options[$type] = [];
                }

                $options[$type][] = $flag->getName();
            }

            $client->store($mailFolderId, $options);
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function createMessageDraft(FolderKey $folderKey, MessageItemDraft $messageItemDraft): MessageItemDraft
    {

        if ($messageItemDraft->getMessageKey()) {
            throw new ImapClientException(
                "Cannot create a MessageItemDraft that already has a MessageKey"
            );
        }

        try {
            $client = $this->connect($folderKey);

            if (!$this->doesMailboxExist($folderKey)) {
                throw new MailFolderNotFoundException(
                    "The mailbox \"{$folderKey->getId()}\" was not found for this account."
                );
            }

            $fullText = $this->getHeaderComposer()->compose("", $messageItemDraft);

            $ids = $client->append($folderKey->getId(), [[
                "data" =>  $fullText,
                "flags" => $messageItemDraft->getFlagList()->resolveToFlags()
            ]]);

            $newKey = new MessageKey(
                $folderKey,
                (string)$ids->ids[0]
            );

            return $messageItemDraft->setMessageKey($newKey);
        } catch (Horde_Imap_Client_Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function createMessageBodyDraft(FolderKey $folderKey, MessageBodyDraft $messageBodyDraft): MessageBodyDraft
    {

        if ($messageBodyDraft->getMessageKey()) {
            throw new ImapClientException(
                "Cannot create a MessageBodyDraft that already has a MessageKey"
            );
        }

        try {
            $mailAccountId = $folderKey->getMailAccountId();
            $mailFolderId = $folderKey->getId();

            $client = $this->connect($folderKey);

            return $this->appendAsDraft(
                $client,
                $mailAccountId,
                $mailFolderId,
                "",
                $messageBodyDraft
            );
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function updateMessageBodyDraft(MessageBodyDraft $messageBodyDraft): MessageBodyDraft
    {

        $key = $messageBodyDraft->getMessageKey();

        if (!$key) {
            throw new ImapClientException(
                "Cannot update a MessageBodyDraft that doesn't have a MessageKey"
            );
        }

        try {
            $mailFolderId = $key->getMailFolderId();
            $mailAccountId = $key->getMailAccountId();

            $client = $this->connect($key);

            $target = $this->getFullMsg($key, $client);

            $newDraft = $this->appendAsDraft(
                $client,
                $mailAccountId,
                $mailFolderId,
                $target,
                $messageBodyDraft
            );

            // delete the previous draft
            $this->deleteMessage($key);

            return $newDraft;
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function updateMessageDraft(MessageItemDraft $messageItemDraft): ?MessageItemDraft
    {

        try {
            $messageKey = $messageItemDraft->getMessageKey();
            $mailFolderId = $messageKey->getMailFolderId();

            $client = $this->connect($messageKey);

            $msg = $this->getFullMsg($messageKey, $client);

            $fullText = $this->getHeaderComposer()->compose($msg, $messageItemDraft);

            $ids = $client->append($mailFolderId, [[
                "data" =>  $fullText,
                "flags" => $messageItemDraft->getFlagList()->resolveToFlags()
            ]]);

            $newKey = new MessageKey(
                $messageKey->getMailAccountId(),
                $messageKey->getMailFolderId(),
                (string)$ids->ids[0]
            );

            $this->deleteMessage($messageKey);

            return $messageItemDraft->setMessageKey($newKey);
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function sendMessageDraft(MessageKey $messageKey): bool
    {
        try {
            $client = $this->connect($messageKey);
            $account = $this->getMailAccount($messageKey);

            $mailFolderId = $messageKey->getMailFolderId();
            $id = $messageKey->getId();

            $rangeList = new Horde_Imap_Client_Ids();
            $rangeList->add($id);

            $fetchQuery = new Horde_Imap_Client_Fetch_Query();
            $fetchQuery->fullText(["peek" => true]);
            $fetchQuery->flags();
            $fetchResult = $client->fetch($mailFolderId, $fetchQuery, ['ids' => $rangeList]);
            $item = $fetchResult[$id];

            // check if message is a draft
            $flags = $item->getFlags();
            if (!in_array(Horde_Imap_Client::FLAG_DRAFT, $flags)) {
                throw new ImapClientException("The specified message is not a Draft-Message.");
            }

            $target = $item->getFullMsg(false);

            $part = Horde_Mime_Part::parseMessage($target);
            $headers = Horde_Mime_Headers::parseHeaders($target);

            // Check for X-CN-DRAFT-INFO...
            $draftInfo = $headers->getHeader("X-CN-DRAFT-INFO");
            $draftInfo = $draftInfo?->value_single;
            // ...delete the header...
            $headers->removeHeader("X-CN-DRAFT-INFO");


            $mail = new Horde_Mime_Mail($headers);
            $mail->setBasePart($part);

            $mailer = $this->getMailer($account);
            $mail->send($mailer);

            // ...and set \Answered flag.
            if ($draftInfo) {
                $this->setAnsweredForDraftInfo($draftInfo, $account->getId());
            }

            return true;
        } catch (Exception $e) {
            if ($e instanceof ImapClientException) {
                throw $e;
            }
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function moveMessage(MessageKey $messageKey, FolderKey $folderKey): MessageKey
    {

        if ($messageKey->getMailAccountId() !== $folderKey->getMailAccountId()) {
            throw new ImapClientException(
                "The \"messageKey\" and the \"folderKey\" do not share the same mailAccountId."
            );
        }

        if ($messageKey->getMailFolderId() === $folderKey->getId()) {
            return $messageKey;
        }


        try {
            $client = $this->connect($messageKey);

            $sourceFolder = $messageKey->getMailFolderId();
            $destFolder = $folderKey->getId();

            $rangeList = new Horde_Imap_Client_Ids();
            $rangeList->add($messageKey->getId());

            $res = $client->copy(
                $sourceFolder,
                $destFolder,
                ["ids" => $rangeList, "move" => true, "force_map" => true]
            );

            if (!is_array($res)) {
                throw new ImapClientException("Moving the message was not successful.");
            }

            return new MessageKey(
                $folderKey->getMailAccountId(),
                $folderKey->getId(),
                (string)$res[$messageKey->getId()]
            );
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }

    // -------------------
    //   Helper
    // -------------------

    /**
     * Returns a MessageItem or a MessageItemDraft, based on $returnItem.
     * @param MessageKey $messageKey
     * @param MessageItemQuery $query
     * @param bool $returnItem
     *
     * @return MessageItem|MessageItemDraft
     *
     * @throws ImapClientException
     */
    protected function getItemOrDraft(
        MessageKey $messageKey,
        MessageItemQuery $query,
        bool $returnItem = true
    ): MessageItem|MessageItemDraft {
        try {
            $client = $this->connect($messageKey);
            $mailFolderId = $messageKey->getMailFolderId();
            $fetchedItems = $this->fetchMessageItems(
                $client,
                new Horde_Imap_Client_Ids($messageKey->getId()),
                $mailFolderId,
                $query
            );

            $ret = $this->buildMessageItem(
                $client,
                new FolderKey($messageKey->getMailAccountId(), $mailFolderId),
                $fetchedItems[0],
                $query->getFields()
            );

            $class = $returnItem === false ? MessageItemDraft::class : MessageItem::class;
            return new $class(
                $ret["messageKey"],
                array_filter($ret["data"], fn ($item) => $item !== null)
            );
        } catch (Exception $e) {
            throw new ImapClientException($e->getMessage(), 0, $e);
        }
    }


    /**
     * Sets the flag \Answered for the message specified in $draftInfo.
     * The string is expected to be a base64-encoded string representing
     * a JSON-encoded array with three indices: mailAccountId, mailFolderId
     * and id.
     * Will do nothing if the mailAccountId in $draftInfo does not match
     * the $forAccountId passed to this method, or if decoding the $xCnDraftInfo
     * failed.
     *
     * @param string $draftInfo
     * @param string $forAccountId
     */
    protected function setAnsweredForDraftInfo(string $draftInfo, string $forAccountId)
    {

        $baseDecode = base64_decode($draftInfo);

        if ($baseDecode === false) {
            return;
        }

        $info = json_decode($baseDecode, true);

        if (!is_array($info) || count($info) !== 3) {
            return;
        }

        if ($info[0] !== $forAccountId) {
            return;
        }

        $messageKey = new MessageKey($forAccountId, $info[1], (string)$info[2]);

        $flagList = new FlagList();
        $flagList[] = new AnsweredFlag(true);

        $this->setFlags($messageKey, $flagList);
    }


    /**
     * Returns the Horde_Mail_Transport to be used with this account.
     *
     *
     * @param MailAccount $account
     *
     * @return Horde_Mail_Transport_Smtphorde|Horde_Mail_Transport|null
     */
    public function getMailer(MailAccount $account): Horde_Mail_Transport_Smtphorde|Horde_Mail_Transport|null
    {

        $account = $this->getMailAccount($account->getId());

        if (!$account) {
            throw new ImapClientException(
                "The passed \"account\" does not share the same id-value with " .
                "the MailAccount this class was configured with."
            );
        }

        if ($this->mailer) {
            return $this->mailer;
        }

        $smtpCfg = [
            "host" => $account->getOutboxAddress(),
            "port" => $account->getOutboxPort(),
            "password" => $account->getOutboxPassword(),
            "username" => $account->getOutboxUser()
        ];

        if ($account->getOutboxSecure()) {
            $smtpCfg["secure"] = $account->getOutboxSecure();
        }

        $this->mailer = new Horde_Mail_Transport_Smtphorde($smtpCfg);

        return $this->mailer;
    }


    /**
     * Appends the specified $rawMessage to $mailFolderId and returns a new MessageBodyDraft with the
     * created MessageKey.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param string $mailAccountId
     * @param string $mailFolderId
     * @param string $target
     * @param MessageBodyDraft $messageBodyDraft
     *
     * @return MessageBodyDraft
     *
     * @throws Horde_Imap_Client_Exception
     */
    protected function appendAsDraft(
        Horde_Imap_Client_Socket $client,
        string $mailAccountId,
        string $mailFolderId,
        string $target,
        MessageBodyDraft $messageBodyDraft
    ): MessageBodyDraft {

        $rawMessage = $this->getBodyComposer()->compose($target, $messageBodyDraft);
        $rawMessage = $this->getHeaderComposer()->compose($rawMessage);

        $ids = $client->append($mailFolderId, [["data" => $rawMessage]]);
        $messageKey = new MessageKey($mailAccountId, $mailFolderId, (string)$ids->ids[0]);

        $flagList = new FlagList();
        $flagList[] = new DraftFlag(true);
        $this->setFlags($messageKey, $flagList);

        return $messageBodyDraft->setMessageKey($messageKey);
    }


    /**
     * Fetches a list of messages from the server, considering start & limit options passed
     * with $options.
     * The property "ids" which may or may not be available in $options should have already been
     * considered and should be found in $searchResultIds.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param Horde_Imap_Client_Ids $searchResultIds
     * @param string $mailFolderId
     * @param MessageItemQuery|MessageItemListQuery $query
     *
     * @return array
     *
     * @throws Horde_Imap_Client_Exception
     * @throws Horde_Imap_Client_Exception_NoSupportExtension
     */
    protected function fetchMessageItems(
        Horde_Imap_Client_Socket $client,
        Horde_Imap_Client_Ids $searchResultIds,
        string $mailFolderId,
        MessageItemQuery|MessageItemListQuery $query
    ): array {

        $start = -1;
        $limit = -1;

        if ($query instanceof MessageItemListQuery) {
            $start = $query->getStart() ?? -1;
            $limit = $query->getLimit() ?? -1;
        }

        if ($start >= 0 && $limit > 0) {
            $rangeList = new Horde_Imap_Client_Ids();
            foreach ($searchResultIds as $key => $entry) {
                if ($key >= $start && $key < $start + $limit) {
                    $rangeList->add($entry);
                }
            }
            $orderedList = $rangeList->ids;
        } else {
            $orderedList = $searchResultIds->ids;
            $rangeList = $searchResultIds;
        }

        // fetch IMAP
        $fetchQuery = new Horde_Imap_Client_Fetch_Query();
        $fetchQuery->flags();
        $fetchQuery->size();
        $fetchQuery->envelope();
        $fetchQuery->structure();

        $fetchQuery->headers("ContentType", ["Content-Type"], ["peek" => true]);
        $fetchQuery->headers("References", ["References"], ["peek" => true]);

        $fetchResult = $client->fetch($mailFolderId, $fetchQuery, ['ids' => $rangeList]);

        $final = [];
        foreach ($orderedList as $id) {
            $final[] = $fetchResult[$id];
        }

        return $final;
    }


    /**
     * Transform the passed $item into an instance of MessageItem.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param FolderKey $key
     * @param $item
     * @param array $fields An array with that specifies the fields to query.
     *
     * @example
     *    $this->buildMessageItem($client, $key, $item, ["size", "hasAttachments"]);
     *
     *
     * @return array an array indexed with "messageKey" and "data" which should both be used to create
     * concrete instances of MessageItem/MessageItemDraft
     *
     * @throws Horde_Imap_Client_Exception
     * @see queryItems
     */
    protected function buildMessageItem(
        Horde_Imap_Client_Socket $client,
        FolderKey $key,
        $item,
        array $fields
    ): array {

        $result = $this->getItemStructure($client, $item, $key, $fields);

        return [
            "messageKey" => $result["messageKey"],
            "data" => $result["data"]
        ];
    }


    /**
     * Transform the passed list of $items to an instance of MessageItemList.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param FolderKey $key
     * @param array $items
     * @param array $fields array of field names that should be considered
     *
     * @return MessageItemList
     *
     * @throws Horde_Imap_Client_Exception
     * @see queryItems
     */
    protected function buildMessageItems(
        Horde_Imap_Client_Socket $client,
        FolderKey $key,
        array $items,
        array $fields
    ): MessageItemList {

        $messageItems = new MessageItemList();

        foreach ($items as $item) {
            $result = $this->getItemStructure($client, $item, $key, $fields);
            $data = $result["data"];

            $messageKey = $result["messageKey"];

            $messageItem = new MessageItem(
                $messageKey,
                $data
            );

            $messageItems[] = $messageItem;
        }

        return $messageItems;
    }


    /**
     * Returns the structure of the requested items as an array, along with additional information,
     * that can be used for constructor-data for AbstractMessageItem.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param $item
     * @param FolderKey $key
     * @param array $fields An array providing "fields" which is numeric array holding
     * fields to return
     *
     * @example
     *  $this->getItemStructure(
     *     $client,
     *     $item,
     *     $key,
     *     ["from", "to", "plain"]
     *   ); // returns "from", "to" and "plain"
     *
     *
     * @return array data with the item structure, options holding additional requested data (passed via $options)
     * and messageKey holding the generated MessageKey for the item.
     *
     * @throws Horde_Imap_Client_Exception
     */
    protected function getItemStructure(
        Horde_Imap_Client_Socket $client,
        $item,
        FolderKey $key,
        array $fields = []
    ): array {

        $wants = function ($key) use ($fields) {
            return in_array($key, $fields);
        };

        $envelope = $item->getEnvelope();
        $flags    = $item->getFlags();

        $messageKey = new MessageKey($key->getMailAccountId(), $key->getId(), (string)$item->getUid());

        $data = [];

        $wants("from")    && $data["from"]    = $this->getAddress($envelope, "from");
        $wants("to")      && $data["to"]      = $this->getAddress($envelope, "to");
        $wants("cc")      && $data["cc"]      = $this->getAddress($envelope, "cc");
        $wants("bcc")     && $data["bcc"]     = $this->getAddress($envelope, "bcc");
        $wants("replyTo") && $data["replyTo"] = $this->getAddress($envelope, "replyTo");

        $wants("seen")     && $data["seen"]     = in_array(Horde_Imap_Client::FLAG_SEEN, $flags);
        $wants("answered") && $data["answered"] = in_array(Horde_Imap_Client::FLAG_ANSWERED, $flags);
        $wants("draft")    && $data["draft"]    = in_array(Horde_Imap_Client::FLAG_DRAFT, $flags);
        $wants("flagged")  && $data["flagged"]  = in_array(Horde_Imap_Client::FLAG_FLAGGED, $flags);
        $wants("recent")   && $data["recent"]   = in_array(Horde_Imap_Client::FLAG_RECENT, $flags);


        $wants("subject") && $data["subject"] = $envelope->subject;
        $wants("date")    && $data["date"]    = $envelope->date ?? new DateTime("1970-01-01 +0000");

        $wants("messageId") && $data["messageId"] = $envelope->message_id;
        $wants("size")      && $data["size"]      = $item->getSize();

        $wants("draftInfo") && $data["draftInfo"] = $item->getHeaders("X-CN-DRAFT-INFO");

        ($wants("charset") || $wants("subject")) &&
        $data["charset"] = $this->getCharsetFromContentTypeHeaderValue(
            $item->getHeaders("ContentType")
        );
        $wants("references") && $data["references"] = $this->getMessageIdStringFromReferencesHeaderValue(
            $item->getHeaders("References")
        );


        $contentData = [];
        if ($wants("hasAttachments")) {
            $messageStructure = $item->getStructure();
            $contentData      = $this->getContents($client, $messageStructure, $messageKey, $fields);

            if ($wants("hasAttachments") && array_key_exists("hasAttachments", $contentData)) {
                $data["hasAttachments"] = $contentData["hasAttachments"];
            }
        }

        return [
            "data"        => $data,
            "contentData" => $contentData,
            "messageKey"  => $messageKey
        ];
    }


    /**
     * Sends a query against the currently connected IMAP server for retrieving
     * a list of messages based on the specified $options.
     *
     * @param Horde_Imap_Client_Socket $client
     * @param FolderKey $key The key of the folder to query
     * @param SortInfoList|null $sortInfo
     * @param Filter|null $filter
     *
     * @return array
     *
     * @throws Horde_Imap_Client_Exception
     */
    protected function queryItems(
        Horde_Imap_Client_Socket $client,
        FolderKey $key,
        ?SortInfoList $sortInfo,
        ?Filter $filter
    ): array {

        $searchOptions = [
            "sort" => $sortInfo
                      ? $sortInfo->toJson($this->sortInfoStrategy)
                      : [Horde_Imap_Client::SORT_REVERSE, Horde_Imap_Client::SORT_DATE]
        ];

        $searchQuery = $filter ? $this->getSearchQueryFromFilter($filter) : null;

        // search and narrow down list
        return $client->search($key->getId(), $searchQuery, $searchOptions);
    }


    /**
     * Returns contents of the mail. Possible return keys are based on the passed
     * $options "fields": "html" (string), "plain" (string) and/or "hasAttachments" (bool)
     *
     * @param Horde_Imap_Client_Socket $client
     * @param $messageStructure
     * @param MessageKey $key
     * @param array $fields an array of fields this method should consider. Possible
     * keys are html, plain, hasAttachments. The values are configuration
     * objects this method should considered.
     *
     *
     * @example
     *   $this->getContents($client, $messageStructure, $key, [
     *      "html", "plain"
     *   ]); // returns full html
     *
     *
     * @return array
     *
     * @throws Horde_Imap_Client_Exception
     */
    protected function getContents(
        Horde_Imap_Client_Socket $client,
        $messageStructure,
        MessageKey $key,
        array $fields = []
    ): array {

        $ret = [];
        $findHtml        = in_array("html", $fields);
        $findPlain       = in_array("plain", $fields);
        $findAttachments = in_array("hasAttachments", $fields);

        $typeMap = $messageStructure->contentTypeMap();
        $bodyQuery = new Horde_Imap_Client_Fetch_Query();

        if ($findAttachments) {
            $ret["hasAttachments"] = false;
            foreach ($typeMap as $part => $type) {
                if (
                    /**
                     * @see conjoon/php-ms-imapuser#48
                     */
                    //in_array($type, ["text/plain", "text/html"]) === false &&
                    $messageStructure->getPart($part)->isAttachment()
                ) {
                    $ret["hasAttachments"] = true;
                }
            }
        }

        if (!$findHtml && !$findPlain) {
            return $ret;
        }

        $getBodyPart = function ($part, $bodyQuery, $clientConf) {
            $length = $clientConf["length"] ?? null;
            $trimApi = $clientConf["trimApi"] ?? null;

            $conf = ["peek" => true];

            if ($length && !$trimApi) {
                $conf["length"] = $length;
            }
            $bodyQuery->bodyPart($part, $conf);
        };

        foreach ($typeMap as $part => $type) {
            if ($type === "text/html" && $findHtml) {
                $getBodyPart($part, $bodyQuery, $findHtml);
            }

            if ($type === "text/plain" && $findPlain) {
                $getBodyPart($part, $bodyQuery, $findPlain);
            }
        }

        $messageData = $client->fetch(
            $key->getMailFolderId(),
            $bodyQuery,
            ['ids' => new Horde_Imap_Client_Ids($key->getId())]
        )->first();

        if ($findHtml) {
            $ret["html"] = $this->getTextContent('html', $messageStructure, $messageData, $typeMap);
        }

        if ($findPlain) {
            $ret["plain"] = $this->getTextContent('plain', $messageStructure, $messageData, $typeMap);
        }


        return $ret;
    }


    /**
     * Helper function for getting content of a message part.
     *
     * @param $type
     * @param $messageStructure
     * @param $messageData
     * @param $typeMap
     *
     * @return array
     */
    protected function getTextContent($type, $messageStructure, $messageData, $typeMap): array
    {

        if (!$messageData) {
            return ["content" => "", "charset" => ""];
        }

        $partId = $messageStructure->findBody($type);

        foreach ($typeMap as $part => $type) {
            if ((string)$part === $partId) {
                $body = $messageStructure->getPart($part);
                $content = $messageData->getBodyPart($part);

                if (!$messageData->getBodyPartDecode($part)) {
                    // Decode the content.
                    $body->setContents($content);
                    $content = $body->getContents();
                }

                return ["content" => $content, "charset" => $body->getCharset()];
            }
        }

        return ["content" => "", "charset" => ""];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function getMessageIdStringFromReferencesHeaderValue(string $value): string
    {

        if (!str_starts_with($value, "References:")) {
            return "";
        }

        return trim(substr($value, 11));
    }

    /**
     * @param $value
     * @return string
     */
    protected function getCharsetFromContentTypeHeaderValue($value): string
    {
        $parts = explode(";", $value);
        foreach ($parts as $part) {
            $part = trim($part);

            $subPart = explode("=", $part);

            if (strtolower(trim($subPart[0])) === "charset") {
                return strtolower(trim($subPart[1]));
            }
        }

        return "";
    }


    /**
     * Helper function for determining if a mailbox is selectable.
     * Will return false if querying the given mailbox would result
     * in an error (server side).
     *
     * @param array $mailbox
     * @return bool
     */
    protected function isMailboxSelectable(array $mailbox): bool
    {
        return !in_array("\\noselect", $mailbox["attributes"]) &&
            !in_array("\\nonexistent", $mailbox["attributes"]);
    }


    /**
     * Helper function to return the MailAddress or MailAddressList based on the
     * specified argument from the $envelope.
     *
     * @param $envelope
     * @param string $type to, from, replyTo, cc or bcc are valid $types
     *
     * @return MailAddress|MailAddressList|null
     */
    protected function getAddress($envelope, string $type): MailAddressList|MailAddress|null
    {

        $type = $type === "replyTo" ? "reply-to" : $type;

        switch ($type) {
            case "from":
            case "reply-to":
                $mailAddress = null;
                if (!$envelope->{$type}) {
                    return null;
                }

                foreach ($envelope->{$type} as $add) {
                    if ($add->bare_address) {
                        $mailAddress = new MailAddress(
                            $add->bare_address,
                            $add->personal ?: $add->bare_address
                        );
                    }
                }
                return $mailAddress;

            default:
                $mailAddressList = new MailAddressList();
                if (!$envelope->{$type}) {
                    return $mailAddressList;
                }
                foreach ($envelope->{$type} as $add) {
                    if ($add->bare_address) {
                        $mailAddressList[] = new MailAddress($add->bare_address, $add->personal ?: $add->bare_address);
                    }
                }
                return $mailAddressList;
        }
    }

    /**
     * @param MessageKey $messageKey
     * @param Horde_Imap_Client_Socket $client
     * @return mixed
     * @throws Horde_Imap_Client_Exception
     * @throws Horde_Imap_Client_Exception_NoSupportExtension
     */
    protected function getFullMsg(MessageKey $messageKey, Horde_Imap_Client_Socket $client): mixed
    {
        $mailFolderId = $messageKey->getMailFolderId();
        $id = $messageKey->getId();

        $rangeList = new Horde_Imap_Client_Ids();
        $rangeList->add($id);

        $fetchQuery = new Horde_Imap_Client_Fetch_Query();
        $fetchQuery->fullText(["peek" => true]);
        $fetchResult = $client->fetch($mailFolderId, $fetchQuery, ['ids' => $rangeList]);

        return $fetchResult[$id]->getFullMsg(false);
    }


    /**
     * Return true if the mailbox represented by $folderKey exists on the server, otehrwise false.
     *
     * @param FolderKey $folderKey
     * @return bool
     * @throws Horde_Imap_Client_Exception
     */
    protected function doesMailboxExist(FolderKey $folderKey): bool
    {
        $client = $this->connect($folderKey);

        $mailboxes = $client->listMailboxes(
            $folderKey->getId(),
            Horde_Imap_Client::MBOX_ALL
        );


        $key = array_keys($mailboxes)[0] ?? null;

        if (!$key) {
            return false;
        }

        return strtolower($key) === strtolower($folderKey->getId());
    }
}
