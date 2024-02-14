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

namespace Tests\Conjoon\MailClient\Service;

use Conjoon\Data\ParameterBag;
use Conjoon\MailClient\Data\Resource\DefaultMailFolderListOptions;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\Folder\MailFolderList;
use Conjoon\MailClient\Folder\Tree\MailFolderTreeBuilder;
use Conjoon\MailClient\MailClient;
use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;
use Conjoon\MailClient\Service\DefaultMailFolderService;
use Conjoon\MailClient\Service\MailFolderService;
use Mockery;
use Tests\TestCase;
use Tests\TestTrait;

class DefaultMailFolderServiceTest extends TestCase
{
    use TestTrait;


    /**
     * Test the instance
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInstance()
    {

        $service = $this->createService();
        $this->assertInstanceOf(MailFolderService::class, $service);
    }


    /**
     * Test getMailFolderChildList()
     *
     * Test expects the list returned by the MailFolderTreeBuilder to be
     * returned by the Service without changing anything.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testGetMailFolderChildList()
    {

        $mailAccount = $this->getTestMailAccount("dev");

        $service = $this->createService();

        $mailFolderList = new MailFolderList();

        $query = $this->createMockForAbstract(
            MailFolderListQuery::class,
            [],
            [new ParameterBag()]
        );

        $service->getMailClient()
                ->shouldReceive("getMailFolderList")
                ->with($mailAccount, $query)
                ->andReturn($mailFolderList);

        $resultList = new MailFolderChildList();

        $service->getMailFolderTreeBuilder()
                ->shouldReceive("listToTree")
                ->with($mailFolderList, $query)
                ->andReturn($resultList);

        $this->assertSame($resultList, $service->getMailFolderChildList($mailAccount, $query));
    }


// +--------------------------------------
// | Helper
// +--------------------------------------
    /**
     * Helper function for creating the service.
     * @return DefaultMailFolderService
     */
    protected function createService(): DefaultMailFolderService
    {
        return new DefaultMailFolderService(
            $this->getMailClientMock(),
            $this->getMailFolderTreeBuilderMock()
        );
    }


    /**
     * Helper function for creating the client Mock.
     * @return mixed
     */
    protected function getMailClientMock()
    {

        return Mockery::mock("overload:" . MailClient::class);
    }


    /**
     * Helper function for creating the client Mock.
     * @return mixed
     */
    protected function getMailFolderTreeBuilderMock()
    {

        return Mockery::mock("overload:" . MailFolderTreeBuilder::class);
    }
}
