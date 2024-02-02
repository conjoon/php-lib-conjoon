<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2023 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Illuminate\Auth\Imap;

use Conjoon\Illuminate\Auth\Imap\DefaultImapUserProvider;
use Conjoon\Illuminate\Auth\LocalMailAccount\LocalAccountProvider;
use Conjoon\Illuminate\Auth\ImapUser;
use Illuminate\Http\Request;
use ReflectionClass;
use Tests\TestCase;

/**
 * Tests LocalAccountProvider.
 */
class LocalAccountProviderTest extends TestCase
{
    public function testClass()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(["header", "route"])
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())->method("header")->with("x-cnmail-data")->willReturn("headerpayload");
        $request->expects($this->once())->method("route")->with("mailAccountId")->willReturn("routeId");

        $provider = new LocalAccountProvider($request);
        $this->assertInstanceOf(DefaultImapUserProvider::class, $provider);

        $payload = $this->makeAccessible($provider, "payload", true);
        $mailAccountId = $this->makeAccessible($provider, "mailAccountId", true);

        $this->assertSame("headerpayload", $payload->getValue($provider));
        $this->assertSame("routeId", $mailAccountId->getValue($provider));
    }


    public function testDecodePayload()
    {
        $provider = new LocalAccountProvider(new Request());
        $decodePayload = $this->makeAccessible($provider, "decodePayload");

        $target = ["encoded" => "json"];
        $str = base64_encode(json_encode($target));

        $this->assertSame($target, $decodePayload->invokeArgs($provider, [$str]));
    }


    public function testGetUser()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(["header", "route"])
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())->method("route")->with("mailAccountId")->willReturn("routeId");

        $conf = [
            "from" => ["address" => "", "name" => "name"],
            "replyTo" => ["address" => "", "name" => "name"],
            "inbox_address" => "address",
            "inbox_port" => 8080,
            "inbox_user" => "inboxUsername",
            "inbox_password" => "inboxPassword",
            "outbox_address" => "address",
            "outbox_port" => 8080,
            "outbox_user" => "user",
            "outbox_password" => "pw",
            "inbox_ssl" => true,
            "outbox_secure" => "tls"
        ];

        $request->expects($this->once())->method("header")->with("x-cnmail-data")->willReturn(
            base64_encode(json_encode($conf))
        );

        $provider = new LocalAccountProvider($request);

        $user = $provider->getUser("inboxUsername", "inboxPassword");
        $this->assertInstanceOf(ImapUser::class, $user);

        $this->assertNull($user->getMailAccount("routeIdNotAvailable"));
        $account = $user->getMailAccount("routeId");

        $toArray = $account->toArray();

        foreach ($conf as $key => $value) {
            $this->assertSame($value, $toArray[$key]);
        }

        $this->assertSame("routeId", $toArray["id"]);
        $this->assertSame("routeId", $toArray["name"]);
    }
}
