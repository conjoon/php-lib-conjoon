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

namespace Conjoon\MailClient\Service;

use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\Folder\Tree\MailFolderTreeBuilder;
use Conjoon\MailClient\MailClient;
use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;

/**
 * Class DefaultMailFolderService.
 * Default implementation of a MailFolderService.
 *
 * @package Conjoon\MailClient\Service
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
    public function getMailFolderChildList(MailAccount $mailAccount, ?MailFolderListQuery $query = null): MailFolderChildList
    {
        $mailFolderList = $this->getMailClient()->getMailFolderList($mailAccount, $query);

        return $this->getMailFolderTreeBuilder()->listToTree($mailFolderList, $query);
    }
}
