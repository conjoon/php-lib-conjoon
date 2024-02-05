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

namespace Tests\Conjoon\MailClient\Data\Transformer\Request;

use Conjoon\Mime\MimeType;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Data\Transformer\Request\DefaultMessageBodyDraftJsonTransformer;
use Conjoon\MailClient\Data\Transformer\Request\MessageBodyDraftJsonTransformer;
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
