<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\JsonApi\Request;

use Conjoon\JsonApi\Request\ResourceUrlParser;
use Conjoon\JsonApi\Request\ResourceUrlRegex;
use Conjoon\JsonApi\Request\ResourceUrlRegexList;
use Tests\TestCase;

/**
 * tests ResourceUrlParser
 */
class ResourceUrlParserTest extends TestCase
{
    /**
     * Tests functionality
     */
    public function testClass()
    {
        $list = $this->createList();
        $template = "{0}resource";
        $collectionTemplate = "{0}collection";

        $parser = new ResourceUrlParser(
            $list,
            $template,
            $collectionTemplate
        );

        $this->assertSame($list, $parser->getResourceUrlRegexList());
        $this->assertSame($template, $parser->getTemplate());
        $this->assertSame($collectionTemplate, $parser->getCollectionTemplate());
    }


    /**
     * Tests parse()
     */
    public function testParse()
    {
        $list = $this->createList();
        $template = "{0}Resource";
        $collectionTemplate = "Collection{0}";

        $parser = new ResourceUrlParser(
            $list,
            $template,
            $collectionTemplate
        );

        $this->assertNull($parser->parse("MailAccounts/MailFolders/INBOX"));
        $this->assertSame(
            "CollectionMessageItem",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );

        $this->assertSame(
            "MessageItemResource",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems/123")
        );

        $parser = new ResourceUrlParser(
            $list,
            $template
        );
        $this->assertNull($parser->getCollectionTemplate());
        $this->assertSame(
            "MessageItemResource",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
    }


    /**
     * Tests representsCollection()
     * @return void
     */
    public function testRepresentsCollection(): void
    {
        $list = $this->createList();
        $template = "{0}Resource";
        $collectionTemplate = "Collection{0}";

        $parser = new ResourceUrlParser(
            $list,
            $template,
            $collectionTemplate
        );

        $this->assertNull(
            $parser->representsCollection("MailAccounts/MailFolders/INBOX")
        );
        $this->assertTrue(
            $parser->representsCollection("MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
        $this->assertFalse(
            $parser->representsCollection("MailAccounts/dev/MailFolders/INBOX/MessageItems/123")
        );
    }


    /**
     * Tests parser without submitting a singeIndex to the reqex
     * @return void
     */
    public function testWithoutSingleIndex()
    {
        $urlRegexList = new ResourceUrlRegexList();
        $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m", 1);

        $template = "{0}Resource";
        $collectionTemplate = "Collection{0}";

        $parser = new ResourceUrlParser(
            $urlRegexList,
            $template,
            $collectionTemplate
        );

        $this->assertSame(
            "CollectionMessageItem",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
        $this->assertSame(
            "CollectionMessageItem",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems/123")
        );

        $parser = new ResourceUrlParser(
            $urlRegexList,
            $template
        );

        $this->assertSame(
            "MessageItemResource",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
        $this->assertSame(
            "MessageItemResource",
            $parser->parse("MailAccounts/dev/MailFolders/INBOX/MessageItems/123")
        );
    }


    /**
     * @return ResourceUrlRegexList
     */
    protected function createList(): ResourceUrlRegexList
    {
        $urlRegexList = new ResourceUrlRegexList();
        $urlRegexList[] = new ResourceUrlRegex("/(MailAccounts)(\/)?[^\/]*$/m", 1, 2);
        $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m", 1, 2);
        $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/(MailFolders)(\/)?[^\/]*$/m", 1, 2);

        return $urlRegexList;
    }
}
