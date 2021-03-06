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

namespace Conjoon\Horde\Mail\Client\Message\Composer;

use Conjoon\Mail\Client\Message\Composer\BodyComposer;
use Conjoon\Mail\Client\Message\MessageBodyDraft;
use Horde_Mime_Headers;
use Horde_Mime_Part;

/**
 * Class HordeBodyComposer
 *
 * @package Conjoon\Horde\Mail\Client\Message\Composer
 */
class HordeBodyComposer implements BodyComposer
{
    /**
     * @inheritdoc
     */
    public function compose(string $target, MessageBodyDraft $messageBodyDraft): string
    {
        $message = Horde_Mime_Part::parseMessage($target);
        $headers = Horde_Mime_Headers::parseHeaders($target);

        $plain = $messageBodyDraft->getTextPlain();
        $html = $messageBodyDraft->getTextHtml();

        $basePart = new Horde_Mime_Part();
        $basePart->setType('multipart/mixed');
        $basePart->isBasePart(true);

        $htmlBody = new Horde_Mime_Part();
        $htmlBody->setType('text/html');
        $htmlBody->setCharset($html->getCharset());
        $htmlBody->setContents($html->getContents());

        $plainBody = new Horde_Mime_Part();
        $plainBody->setType('text/plain');
        $plainBody->setCharset($plain->getCharset());
        $plainBody->setContents($plain->getContents());

        $basePart[] = $htmlBody;
        $basePart[] = $plainBody;

        foreach ($message as $part) {
            if ($part->isAttachment()) {
                $basePart[] = $part;
            }
        }

        $headers = $basePart->addMimeHeaders(["headers" => $headers]);

        return trim($headers->toString()) .
            "\n\n" .
            trim($basePart->toString());
    }
}
