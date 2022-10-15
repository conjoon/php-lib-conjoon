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

namespace Conjoon\MailClient\Protocol\Http\Request\Transformer;

use Conjoon\Core\Data\MimeType;
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
