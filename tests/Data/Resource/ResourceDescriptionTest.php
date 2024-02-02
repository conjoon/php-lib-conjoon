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

namespace Tests\Conjoon\Data\Resource;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests ResourceDescription
 */
class ResourceDescriptionTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $resource = $this->getResourceDescriptionMock();

        $this->assertInstanceOf(ResourceDescription::class, $resource);
    }


    /**
     * tests getAllRelationshipTypes()
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipTypes(): void
    {
        $translator = $this->getResourceDescriptionMock(["getAllRelationshipResourceDescriptions"]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget->expects($this->exactly(1))->method("getType")->willReturn("entity");
        $resourceTarget_1 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->exactly(2))->method("getType")->willReturn("entity_1");
        $resourceTarget_2 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_2->expects($this->exactly(2))->method("getType")->willReturn("entity_2");

        $relationships1 = new ResourceDescriptionList();
        $relationships1[] = $resourceTarget_1;
        $relationships1[] = $resourceTarget_2;

        $relationships2 = new ResourceDescriptionList();
        $relationships2[] = $resourceTarget;
        $relationships2[] = $resourceTarget_1;
        $relationships2[] = $resourceTarget_2;

        $translator
            ->expects($this->exactly(2))
            ->method("getAllRelationshipResourceDescriptions")
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(
                $relationships1,
                $relationships2,
            );


        $getAllRelationshipTypes = $reflection->getMethod("getAllRelationshipTypes");
        $getAllRelationshipTypes->setAccessible(true);

        $this->assertEquals([
            "entity_1", "entity_2"
        ], $getAllRelationshipTypes->invokeArgs($translator, []));

        $this->assertEquals([
            "entity", "entity_1", "entity_2"
        ], $getAllRelationshipTypes->invokeArgs($translator, [true]));
    }


    /**
     * tests getAllRelationshipPaths() with dot-notation
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipPaths(): void
    {
        $relationships = new ResourceDescriptionList();

        $resourceTarget = $this->getResourceDescriptionMock([
            "getRelationships",
            "getAllRelationshipResourceDescriptions"
        ]);
        $resourceTarget->expects($this->any())->method("getType")->willReturn("entity");
        $resourceTarget->expects($this->any())->method("getRelationships")->willReturn($relationships);
        $reflection = new ReflectionClass($resourceTarget);


        $relationships_1 = new ResourceDescriptionList();
        $relationships_1_1 = new ResourceDescriptionList();
        $relationships_1_2 = new ResourceDescriptionList();


        $resourceTarget_1 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->any())->method("getType")->willReturn("entity_1");
        $resourceTarget_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1);

        $resourceTarget_1_1 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1");
        $resourceTarget_1_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1_1);

        $resourceTarget_1_2 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2->expects($this->any())->method("getType")->willReturn("entity_1_2");
        $resourceTarget_1_2->expects($this->any())->method("getRelationships")->willReturn($relationships_1_2);

        $resourceTarget_1_1_1 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1_1");

        $resourceTarget_1_2_1 = $this->getResourceDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2_1->expects($this->any())->method("getType")->willReturn("entity_1_2_1");

        $relationships[] = $resourceTarget_1;
        $relationships_1[] = $resourceTarget_1_1;
        $relationships_1[] = $resourceTarget_1_2;
        $relationships_1_1[] = $resourceTarget_1_1_1;
        $relationships_1_2[] = $resourceTarget_1_2_1;


        /**
         * [
         *  "entity",
         *  "entity_1",
         *  "entity_1.entity_1_1",
         *  "entity_1.entity_1_1.entity_1_1_1",
         *  "entity_1.entity_1_2",
         *  "entity_1.entity_1_2.entity_1_2_1"
         * ]
         */

        $getAllRelationshipPaths = $reflection->getMethod("getAllRelationshipPaths");
        $getAllRelationshipPaths->setAccessible(true);

        $this->assertEquals([
            "entity",
            "entity.entity_1",
            "entity.entity_1.entity_1_1",
            "entity.entity_1.entity_1_1.entity_1_1_1",
            "entity.entity_1.entity_1_2",
            "entity.entity_1.entity_1_2.entity_1_2_1"
        ], $getAllRelationshipPaths->invokeArgs($resourceTarget, [true]));

        $this->assertEquals([
            "entity_1",
            "entity_1.entity_1_1",
            "entity_1.entity_1_1.entity_1_1_1",
            "entity_1.entity_1_2",
            "entity_1.entity_1_2.entity_1_2_1"
        ], $getAllRelationshipPaths->invokeArgs($resourceTarget, [false]));
    }



    /**
     * Tests getAllRelationshipResourceDescriptions
     * @return void
     */
    public function testGetAllRelationshipResourceDescriptions(): void
    {
        $resourceTarget = $this->getResourceDescriptionMock(["getRelationships"], "A");

        $resourceTarget_1_1 = $this->getResourceDescriptionMock(["getRelationships"], "B");
        $resourceTarget_1_2 = $this->getResourceDescriptionMock(["getRelationships"], "C");
        $resourceTarget_2_1 = $this->getResourceDescriptionMock(["getRelationships"], "D");

        $relationships = new ResourceDescriptionList();
        $relationships[] = $resourceTarget_1_1;
        $relationships[] = $resourceTarget_1_2;

        $relationships_1_1 = new ResourceDescriptionList();
        $relationships_1_1[] = $resourceTarget_2_1;

        $relationships_1_2 = new ResourceDescriptionList();
        $relationships_2_1 = new ResourceDescriptionList();

        $callTimes = 2;


        $resourceTarget->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships
        );

        $resourceTarget_1_1->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_1_1
        );
        $resourceTarget_1_2->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_1_2
        );
        $resourceTarget_2_1->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_2_1
        );


        $list = $resourceTarget->getAllRelationshipResourceDescriptions();
        foreach (
            [
                $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
            ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }

        $list = $resourceTarget->getAllRelationshipResourceDescriptions(true);
        foreach (
            [
                $resourceTarget, $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
            ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }
    }


    /**
     * @param array<int, string> $methods
     * @param string $type
     * @return MockObject&ResourceDescription
     */
    protected function getResourceDescriptionMock(
        array $methods = [],
        string $type = ""
    ): MockObject&ResourceDescription {
        if (!in_array("getType", $methods)) {
            $methods[] = "getType";
        }

        /**
         * @var MockObject&ResourceDescription $mock
         */
        $mock = $this->createMockForAbstract(
            ResourceDescription::class,
            $methods
        );

        if ($type !== "") {
            $mock->expects($this->any())->method("getType")->willReturn($type);
        }

        return $mock;
    }
}
