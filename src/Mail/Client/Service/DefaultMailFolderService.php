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

namespace Conjoon\Mail\Client\Service;

use Conjoon\Mail\Client\Data\MailAccount;
use Conjoon\Mail\Client\Folder\MailFolderChildList;
use Conjoon\Mail\Client\Folder\Tree\MailFolderTreeBuilder;
use Conjoon\Mail\Client\MailClient;

/**
 * Class DefaultMailFolderService.
 * Default implementation of a MailFolderService.
 *
 * @package Conjoon\Mail\Client\Service
 */
class DefaultMailFolderService implements MailFolderService
{
    /**
     * @var MailClient
     */
    protected MailClient $mailClient;


    /**
     * @var MailFolderTreeBuilder
     */
    protected MailFolderTreeBuilder $mailFolderTreeBuilder;


    /**
     * DefaultMailFolderService constructor.
     * @param MailFolderTreeBuilder $mailFolderTreeBuilder
     * @param MailClient $client
     */
    public function __construct(MailClient $client, MailFolderTreeBuilder $mailFolderTreeBuilder)
    {
        $this->mailClient = $client;
        $this->mailFolderTreeBuilder = $mailFolderTreeBuilder;
    }


    /**
     * Returns the MailFolderTreeBuilder used with this instance.
     * @return MailFolderTreeBuilder
     */
    public function getMailFolderTreeBuilder(): MailFolderTreeBuilder
    {
        return $this->mailFolderTreeBuilder;
    }


// +-------------------------------
// | MailFolderService
// +-------------------------------

    /**
     * @inheritdoc
     */
    public function getMailClient(): MailClient
    {
        return $this->mailClient;
    }


    /**
     * @inheritdoc
     */
    public function getMailFolderChildList(MailAccount $mailAccount, array $subscriptions = []): MailFolderChildList
    {
        $mailFolderList = $this->getMailClient()->getMailFolderList($mailAccount);

        return $this->getMailFolderTreeBuilder()->listToTree($mailFolderList, $subscriptions);
    }
}
