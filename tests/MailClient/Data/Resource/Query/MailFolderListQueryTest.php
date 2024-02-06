<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\ParameterBag;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;
use Tests\TestCase;


class MailFolderListQueryTest extends TestCase
{
    /**
     * test class
     */
    public function testClass()
    {
        $inst = $this->createMockForAbstract(MailFolderListQuery::class, [], [new ParameterBag()]);

        $this->assertInstanceOf(MailFolderDescription::class, $inst->getResourceDescription(
        ));
    }
}
