<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2023 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Illuminate\Auth;

use Conjoon\Illuminate\Auth\Imap\Conjoon;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Interface ImapUserProvider
 *
 * @package Conjoon\Illuminate\Auth\Imap
 */
interface ImapUserProvider extends UserProvider
{
    /**
     * Queries this ImapUserProvider and returns ImapUser that is associated
     * with the specified $username and $password, or null if no match was found.
     *
     * @param string $username
     * @param string $password
     *
     * @return  ImapUser|null
     */
    public function getUser(string $username, string $password): ?ImapUser;
}
