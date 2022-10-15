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

namespace Tests\Conjoon\MailClient\Data\CompoundKey;

use Conjoon\MailClient\Data\CompoundKey\CompoundKey;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\Core\Contract\Stringable;
use Tests\TestCase;

/**
 * Class FolderKeyTest
 * @package Tests\Conjoon\MailClient\Data\CompoundKey
 */
class FolderKeyTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------
    /**
     * Test class
     */
    public function testClass()
    {

        $mailAccountId = "dev";
        $id = "INBOX";
        $key = new FolderKey($mailAccountId, $id);

        $this->assertInstanceOf(CompoundKey::class, $key);
        $this->assertInstanceOf(Stringable::class, $key);
        $this->assertSame($mailAccountId, $key->getMailAccountId());
        $this->assertSame($id, $key->getId());

        $this->assertEquals(["mailAccountId" => $mailAccountId, "id" => $id], $key->toArray());

        $this->assertSame(json_encode($key->toArray()), $key->toString());

        $mailAccount = new MailAccount(["id" => "dev"]);
        $key = new FolderKey($mailAccount, $id);
        $this->assertSame($mailAccount->getId(), $key->getMailAccountId());
    }
}
