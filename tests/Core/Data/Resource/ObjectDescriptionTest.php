<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Core\Data\Resource;

use Conjoon\Core\Data\Resource\ObjectDescription;
use Conjoon\Core\Data\Resource\ObjectDescriptionList;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests ObjectDescription
 */
class ObjectDescriptionTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $resource = $this->getObjectDescriptionMock();

        $this->assertInstanceOf(ObjectDescription::class, $resource);
    }


    /**
     * tests getAllRelationshipTypes()
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipTypes()
    {
        $translator = $this->getObjectDescriptionMock(["getAllRelationshipResourceDescriptions"]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget->expects($this->exactly(1))->method("getType")->willReturn("entity");
        $resourceTarget_1 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->exactly(2))->method("getType")->willReturn("entity_1");
        $resourceTarget_2 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_2->expects($this->exactly(2))->method("getType")->willReturn("entity_2");

        $relationships1 = new ObjectDescriptionList();
        $relationships1[] = $resourceTarget_1;
        $relationships1[] = $resourceTarget_2;

        $relationships2 = new ObjectDescriptionList();
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
     * tests getAllRelationshipPaths() with dotnotation
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipPaths()
    {
        $relationships = new ObjectDescriptionList();

        $resourceTarget = $this->getObjectDescriptionMock([
            "getRelationships",
            "getAllRelationshipResourceDescriptions"
        ]);
        $resourceTarget->expects($this->any())->method("getType")->willReturn("entity");
        $resourceTarget->expects($this->any())->method("getRelationships")->willReturn($relationships);
        $reflection = new ReflectionClass($resourceTarget);


        $relationships_1 = new ObjectDescriptionList();
        $relationships_1_1 = new ObjectDescriptionList();
        $relationships_1_2 = new ObjectDescriptionList();


        $resourceTarget_1 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->any())->method("getType")->willReturn("entity_1");
        $resourceTarget_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1);

        $resourceTarget_1_1 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1");
        $resourceTarget_1_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1_1);

        $resourceTarget_1_2 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2->expects($this->any())->method("getType")->willReturn("entity_1_2");
        $resourceTarget_1_2->expects($this->any())->method("getRelationships")->willReturn($relationships_1_2);

        $resourceTarget_1_1_1 = $this->getObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1_1");

        $resourceTarget_1_2_1 = $this->getObjectDescriptionMock(["getRelationships"]);
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
     * @throws ReflectionException
     */
    public function testGetAllRelationshipResourceDescriptions(): void
    {
        $resourceTarget = $this->getObjectDescriptionMock(["getRelationships"], "A");
        $reflection = new ReflectionClass($resourceTarget);

        $resourceTarget_1_1 = $this->getObjectDescriptionMock(["getRelationships"], "B");
        $resourceTarget_1_2 = $this->getObjectDescriptionMock(["getRelationships"], "C");
        $resourceTarget_2_1 = $this->getObjectDescriptionMock(["getRelationships"], "D");

        $relationships = new ObjectDescriptionList();
        $relationships[] = $resourceTarget_1_1;
        $relationships[] = $resourceTarget_1_2;

        $relationships_1_1 = new ObjectDescriptionList();
        $relationships_1_1[] = $resourceTarget_2_1;

        $relationships_1_2 = new ObjectDescriptionList();
        $relationships_2_1 = new ObjectDescriptionList();

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
     * @param array $methods
     * @return MockObject
     */
    protected function getObjectDescriptionMock(array $methods = [], $type = ""): MockObject
    {
        if (!in_array("getType", $methods)) {
            $methods[] = "getType";
        }

        $mock = $this->createMockForAbstract(
            ObjectDescription::class,
            $methods
        );

        if ($type !== "") {
            $mock->expects($this->any())->method("getType")->willReturn($type);
        }

        return $mock;
    }
}
