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

namespace Tests\Conjoon\MailClient\Data\Protocol\Http\Request\Transformer;

use Conjoon\Mime\MimeType;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Data\Protocol\Http\Request\Transformer\DefaultMessageBodyDraftJsonTransformer;
use Conjoon\MailClient\Data\Protocol\Http\Request\Transformer\MessageBodyDraftJsonTransformer;
use Conjoon\Core\Contract\JsonDecodable;
use Tests\TestCase;

/**
 * Tests  DefaultMessageBodyDraftJsonTransformer.
 */
class DefaultMessageBodyDraftJsonTransformerTest extends TestCase
{
    /**
     * Test inheritance
     */
    public function testClass()
    {

        $writer = new DefaultMessageBodyDraftJsonTransformer();
        $this->assertInstanceOf(MessageBodyDraftJsonTransformer::class, $writer);
        $this->assertInstanceOf(JsonDecodable::class, $writer);
    }

    /**
     * Test from Array
     */
    public function testFromArray()
    {

        $writer = new DefaultMessageBodyDraftJsonTransformer();

        $data = [
            "mailAccountId" => "a",
            "mailFolderId" => "b",
            "id" => "c",
            "textHtml" => "foo",
            "textPlain" => "bar"
        ];

        $draft = $writer::fromArray($data);

        $this->assertInstanceOf(MessageBodyDraft::class, $draft);

        $this->assertSame($data["mailAccountId"], $draft->getMessageKey()->getMailAccountId());
        $this->assertSame($data["mailFolderId"], $draft->getMessageKey()->getMailFolderId());
        $this->assertSame($data["id"], $draft->getMessageKey()->getId());
        $this->assertSame($data["textHtml"], $draft->getTextHtml()->getContents());
        $this->assertSame($data["textPlain"], $draft->getTextPlain()->getContents());

        $this->assertSame("UTF-8", $draft->getTextPlain()->getCharset());
        $this->assertSame("UTF-8", $draft->getTextHtml()->getCharset());

        $this->assertSame(MimeType::TEXT_PLAIN, $draft->getTextPlain()->getMimeType());
        $this->assertSame(MimeType::TEXT_HTML, $draft->getTextHtml()->getMimeType());
    }


    /**
     * Test fromArray no message key
     */
    public function testFromArrayNoKey()
    {

        $writer = new DefaultMessageBodyDraftJsonTransformer();

        $data = [
            "textHtml" => "foo",
            "textPlain" => "bar"
        ];

        $draft = $writer::fromArray($data);

        $this->assertInstanceOf(MessageBodyDraft::class, $draft);

        $this->assertNull($draft->getMessageKey());
        $this->assertSame($data["textHtml"], $draft->getTextHtml()->getContents());
        $this->assertSame($data["textPlain"], $draft->getTextPlain()->getContents());
    }


    /**
     * Test from Array no data
     */
    public function testFromArrayAllDataMissing()
    {

        $writer = new DefaultMessageBodyDraftJsonTransformer();

        $data = [
            "mailAccountId" => "a",
            "mailFolderId" => "b",
            "id" => "c"
        ];

        $draft = $writer::fromArray($data);

        $this->assertInstanceOf(MessageBodyDraft::class, $draft);

        $this->assertNull($draft->getTextHtml());
        $this->assertNull($draft->getTextPlain());
    }
}
