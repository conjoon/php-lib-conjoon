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

namespace Tests\Conjoon\MailClient\Message;

use Conjoon\Mime\MimeType;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Message\AbstractMessageBody;
use Conjoon\MailClient\Message\MessageBody;
use Conjoon\MailClient\Message\MessagePart;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Class MessageBodyTest
 * @package Tests\Conjoon\MailClient\Message
 */
class MessageBodyTest extends TestCase
{
    use JsonableTestTrait;

// ---------------------
//    Tests
// ---------------------
    /**
     * Test class
     */
    public function testClass()
    {
        $messageKey = new MessageKey("dev", "INBOX", "232");
        $body = $this->createMessageBody($messageKey);

        $this->assertInstanceOf(AbstractMessageBody::class, $body);

        $this->assertSame($messageKey, $body->getMessageKey());

        $this->assertEquals([
            "type" => "MessageBody",
            "mailAccountId" => "dev",
            "mailFolderId" => "INBOX",
            "id" => "232",
            "textPlain" => "foo",
            "textHtml" => "<b>bar</b>"
        ], $body->toArray());
    }


    /**
     * Test toJson
     */
    public function testToJson()
    {
        $body = $this->createMessageBody();

        $this->runToJsonTest($body);
    }


    /**
     * @return void
     */
    protected function createMessageBody($messageKey = null)
    {
        if ($messageKey === null) {
            $messageKey = new MessageKey("dev", "INBOX", "232");
        }

        $body = new MessageBody($messageKey);

        $plainPart = new MessagePart("foo", "ISO-8859-1", MimeType::TEXT_PLAIN);
        $htmlPart = new MessagePart("<b>bar</b>", "UTF-8", MimeType::TEXT_HTML);

        $body->setTextPlain($plainPart);
        $body->setTextHtml($htmlPart);

        return $body;
    }
}
