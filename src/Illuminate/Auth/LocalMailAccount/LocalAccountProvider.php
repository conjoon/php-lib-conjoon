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

namespace Conjoon\Illuminate\Auth\LocalMailAccount;

use Conjoon\Illuminate\Auth\Imap\DefaultImapUserProvider;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\Mail\Client\Data\MailAccount;
use Illuminate\Http\Request;

/**
 * Auth provider that expects required mailserver configurations to be available
 * with the payload ("x-cnmail-data"-header).
 */
class LocalAccountProvider extends DefaultImapUserProvider
{
    /**
     * @var string
     */
    private $payload;

    /**
     * @var string
     */
    private $mailAccountId;


    public function __construct(Request $request)
    {
        $this->payload       = $request->header("x-cnmail-data");
        $this->mailAccountId =  $request->route('mailAccountId');
    }


    public function getUser(string $username, string $password): ?ImapUser
    {
        $config = $this->decodePayload($this->payload);
        $mailAccount = $this->createMailAccount($username, $password, $config);

        return new ImapUser($username, $password, $mailAccount);
    }

    private function createMailAccount(string $username, string $password, array $config): MailAccount
    {
        $merged = array_merge($config, [
            "id" => $this->mailAccountId,
            "name" => $this->mailAccountId,
            "inbox_user" => $username,
            "inbox_password" => $password
        ]);
        return new MailAccount($merged);
    }

    private function decodePayload(string $payload): array
    {
        return json_decode(base64_decode($payload), true);
    }

}
