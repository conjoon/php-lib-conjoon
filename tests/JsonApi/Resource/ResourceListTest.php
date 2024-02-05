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

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\Core\AbstractList;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceList;
use Tests\TestCase;

class ResourceListTest extends TestCase
{
    /**
     * Tests constructor
     */
    public function testClass(): void
    {
        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(Resource::class, $list->getEntityType());
    }


    /**
     * @return ResourceList
     */
    protected function createList(): ResourceList
    {
        $list = new ResourceList();


        return $list;
    }
}
