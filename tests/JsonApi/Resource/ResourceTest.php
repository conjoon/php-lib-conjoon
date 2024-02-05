<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\JsonApi\Resource\Resource;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    /**
     * Tests constructor
     */
    public function testClass(): void
    {
        $jsonable = new TestJson();

        $resource = new Resource($jsonable);

        $this->assertEquals(
            ["json" => "value"],
            $resource->toJson()
        );
    }

}

class TestJson implements Jsonable, Arrayable {
    public function toJson(JsonStrategy $strategy = null): array {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return ["json" => "value"];
    }
}