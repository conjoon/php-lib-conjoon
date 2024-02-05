<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Data\Transformer\Request;

use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\MailAddress;
use Conjoon\MailClient\Data\MailAddressList;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Exception\JsonDecodeException;
use DateTime;
use Exception;

/**
 * Class DefaultMessageItemDraftJsonTransformer
 * This implementation will at least set the "date" field to the value of NOW if the
 * field is missing in the $data-array. All other fields will only be set if they are
 * available in $data.
 * This implementation considers the Modifiable state of a MessageItemDraft and
 * will not pass the values to the constructor to make sure the data changed here is marked as
 * modified.
 *
 * @package Conjoon\MailClient\Message\Request\Transformer
 */
class DefaultMessageItemDraftJsonTransformer implements MessageItemDraftJsonTransformer
{
    /**
     * @inheritdoc
     * @param string $value
     * @return MessageItemDraft|Jsonable
     *
     * @throws Exception
     * @see fromArray
     */
    public static function fromString(string $value): MessageItemDraft
    {
        $value = json_decode($value, true);

        if (!$value) {
            throw new JsonDecodeException("cannot decode from string");
        }
        return self::fromArray($value);
    }


    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function fromArray(array $arr): MessageItemDraft
    {

        if (!isset($arr["mailAccountId"]) || !isset($arr["mailFolderId"]) || !isset($arr["id"])) {
            throw new JsonDecodeException(
                "Missing compound key information. " .
                "Fields \"mailAccountId\", \"mailFolderId\", and \"id\" must be set."
            );
        }

        // consider Modifiable and do not pass to constructor
        $messageKey = new MessageKey($arr["mailAccountId"], $arr["mailFolderId"], $arr["id"]);
        $draft = new MessageItemDraft($messageKey);

        foreach ($arr as $field => $value) {
            if (in_array($field, ["mailAccountId", "mailFolderId", "id"])) {
                continue;
            }

            $setter = "set" . ucfirst($field);

            switch ($field) {
                case "from":
                case "replyTo":
                    try {
                        $add = MailAddress::fromString($value);
                    } catch (JsonDecodeException $e) {
                        $add = null;
                    }
                    $draft->{$setter}($add);
                    break;

                case "to":
                case "cc":
                case "bcc":
                    try {
                        $add = MailAddressList::fromString($value);
                    } catch (JsonDecodeException $e) {
                        $add = null;
                    }
                    $draft->{$setter}($add);
                    break;

                case "date":
                    $draft->{$setter}(new DateTime("@" . $arr["date"]));
                    break;

                default:
                    $draft->{$setter}($value);
                    break;
            }
        }

        if (!$draft->getDate()) {
            $draft->setDate(new DateTime());
        }

        return $draft;
    }
}
