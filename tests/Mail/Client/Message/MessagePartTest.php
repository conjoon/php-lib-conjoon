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

namespace Tests\Conjoon\Mail\Client\Message;

use Conjoon\Mail\Client\Message\MessagePart;
use Conjoon\Util\Copyable;
use Tests\TestCase;

/**
 * Class MessagePartTest
 * @package Tests\Conjoon\Mail\Client\Message
 */
class MessagePartTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------
    /**
     * Test class
     */
    public function testClass()
    {


        $messagePart = new MessagePart("foo", "bar", "text/html");

        $this->assertInstanceOf(Copyable::class, $messagePart);

        $this->assertSame("foo", $messagePart->getContents());
        $this->assertSame("bar", $messagePart->getCharset());
        $this->assertSame("text/html", $messagePart->getMimeType());

        $messagePart->setContents("contents", "charset");

        $this->assertSame("contents", $messagePart->getContents());
        $this->assertSame("charset", $messagePart->getCharset());
        $this->assertSame("text/html", $messagePart->getMimeType());

        $copy = $messagePart->copy();
        $this->assertNotSame($copy, $messagePart);
        $this->assertEquals($copy, $messagePart);
    }
}
