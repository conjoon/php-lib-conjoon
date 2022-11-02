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

namespace Tests\Conjoon\MailClient\Service;

use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Service\DefaultAuthService;
use Conjoon\MailClient\Service\AuthService;
use Horde_Imap_Client_Socket;
use Horde_Imap_Client_Exception;
use Mockery;
use Tests\TestCase;
use Tests\TestTrait;

/**
 * Class DefaultAuthServiceTest
 *
 */
class DefaultAuthServiceTest extends TestCase
{
    use TestTrait;


// ------------------
//     Tests
// ------------------

    /**
     * Tests constructor.
     *
     */
    public function testInstance()
    {
        $service = new DefaultAuthService();

        $this->assertInstanceOf(AuthService::class, $service);
    }


    /**
     * Tests authenticate.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthenticate()
    {
        $service = new DefaultAuthService();

        $mailAccount = $this->createMailAccount();

        $imapStub = Mockery::mock("overload:" . Horde_Imap_Client_Socket::class);

        $imapStub
            ->shouldReceive("__construct")
            ->with([
                "username" => $mailAccount->getInboxUser(),
                "password" => $mailAccount->getInboxPassword(),
                "hostspec" => $mailAccount->getInboxAddress(),
                "port" => $mailAccount->getInboxPort(),
                "secure" => $mailAccount->getInboxSsl() ? "ssl" : null
            ]);

        $imapStub->shouldReceive("login");
        $this->assertTrue($service->authenticate($mailAccount));
    }


    /**
     * Tests authenticate.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthenticateThrows()
    {
        $service = new DefaultAuthService();

        $mailAccount = $this->createMailAccount();

        $imapStub = Mockery::mock("overload:" . Horde_Imap_Client_Socket::class);
        $imapStub->shouldReceive("login")->andThrow(new Horde_Imap_Client_Exception());

        $this->assertFalse($service->authenticate($mailAccount));
    }


    /**
     * @return MailAccount
     */
    protected function createMailAccount()
    {
        return new MailAccount([
            "inbox_user" => "inbox user",
            "inbox_password" => "inbox password",
            "inbox_address" => "inbox address",
            "inbox_port" => 123,
            "inbox_ssl" => true
         ]);
    }
}
