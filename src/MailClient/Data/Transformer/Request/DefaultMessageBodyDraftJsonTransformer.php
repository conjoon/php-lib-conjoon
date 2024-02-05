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

use Conjoon\Mime\MimeType;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessagePart;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Exception\JsonDecodeException;
use Exception;

/**
 * Class DefaultMessageBodyDraftJsonTransformer
 *
 * @package Conjoon\MailClient\Message\Request\Transformer
 */
class DefaultMessageBodyDraftJsonTransformer implements MessageBodyDraftJsonTransformer
{
    /**
     * @inheritdoc
     * @param string $value
     * @return MessageBodyDraft|Jsonable
     *
     * @throws Exception
     * @see fromArray
     */
    public static function fromString(string $value): MessageBodyDraft
    {
        $value = json_decode($value, true);

        if (!$value) {
            throw new JsonDecodeException("cannot decode from string");
        }
        return self::fromArray($value);
    }


    /**
     * @inheritdoc
     */
    public static function fromArray(array $arr): MessageBodyDraft
    {

        $textHtml = null;
        $textPlain = null;

        if (isset($arr["textPlain"])) {
            $textPlain = new MessagePart($arr["textPlain"], "UTF-8", MimeType::TEXT_PLAIN);
        }

        if (isset($arr["textHtml"])) {
            $textHtml = new MessagePart($arr["textHtml"], "UTF-8", MimeType::TEXT_HTML);
        }

        $messageKey = null;

        if (isset($arr["mailAccountId"]) && isset($arr["mailFolderId"]) && isset($arr["id"])) {
            $messageKey = new MessageKey($arr["mailAccountId"], $arr["mailFolderId"], $arr["id"]);
        }

        $messageBodyDraft = new MessageBodyDraft($messageKey);
        $textHtml && $messageBodyDraft->setTextHtml($textHtml);
        $textPlain && $messageBodyDraft->setTextPlain($textPlain);

        return $messageBodyDraft;
    }
}
