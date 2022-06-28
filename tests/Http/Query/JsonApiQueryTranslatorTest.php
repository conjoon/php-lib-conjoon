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

namespace Tests\Conjoon\Http\Query;

use Conjoon\Core\ParameterBag;
use Conjoon\Http\Query\InvalidParameterResourceException;
use Conjoon\Http\Query\InvalidQueryException;
use Conjoon\Http\Query\JsonApiQueryTranslator;
use Conjoon\Http\Query\QueryTranslator;
use Conjoon\Core\ResourceQuery;
use Conjoon\Http\Resource\ResourceObjectDescription;
use Conjoon\Http\Resource\ResourceObjectDescriptionList;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class JsonApiQueryTranslatorTest
 * @package Tests\Conjoon\Http\Query
 */
class JsonApiQueryTranslatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $translator = $this->getQueryTranslator();
        $this->assertInstanceOf(QueryTranslator::class, $translator);
    }


    /**
     * Tests getRelatedResourceTargets
     * @return void
     */
    public function testGetRelatedResourceTargets()
    {
        $translator = $this->getQueryTranslator(["getResourceTarget"]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceObjectDescriptionMock(["getRelationships"]);

        $resourceTarget_1_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_2_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);

        $relationships = new ResourceObjectDescriptionList();
        $relationships[] = $resourceTarget_1_1;
        $relationships[] = $resourceTarget_1_2;

        $relationships_1_1 = new ResourceObjectDescriptionList();
        $relationships_1_1[] = $resourceTarget_2_1;

        $relationships_1_2 = new ResourceObjectDescriptionList();
        $relationships_2_1 = new ResourceObjectDescriptionList();

        $callTimes = 2;

        $translator->expects($this->exactly($callTimes + 1))->method("getResourceTarget")->willReturn(
            $resourceTarget
        );
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


        $getRelatedResourceTargetsReflection = $reflection->getMethod("getRelatedResourceTargets");
        $getRelatedResourceTargetsReflection->setAccessible(true);

        $list = $getRelatedResourceTargetsReflection->invokeArgs($translator, []);
        foreach (
            [
            $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
            ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }

        $list = $getRelatedResourceTargetsReflection->invokeArgs($translator, [true]);
        foreach (
            [
                     $resourceTarget, $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
                 ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }
    }


    /**
     * tests getRelatedResourceTargetTypes()
     * @return void
     */
    public function testGetRelatedResourceTargetTypes()
    {
        $translator = $this->getQueryTranslator(["getRelatedResourceTargets"]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget->expects($this->exactly(1))->method("getType")->willReturn("entity");
        $resourceTarget_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->exactly(2))->method("getType")->willReturn("entity_1");
        $resourceTarget_2 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_2->expects($this->exactly(2))->method("getType")->willReturn("entity_2");

        $relationships1 = new ResourceObjectDescriptionList();
        $relationships1[] = $resourceTarget_1;
        $relationships1[] = $resourceTarget_2;

        $relationships2 = new ResourceObjectDescriptionList();
        $relationships2[] = $resourceTarget;
        $relationships2[] = $resourceTarget_1;
        $relationships2[] = $resourceTarget_2;

        $translator
            ->expects($this->exactly(2))
            ->method("getRelatedResourceTargets")
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(
                $relationships1,
                $relationships2,
            );


        $getRelatedResourceTargetTypesReflection = $reflection->getMethod("getRelatedResourceTargetTypes");
        $getRelatedResourceTargetTypesReflection->setAccessible(true);

        $this->assertEquals([
            "entity_1", "entity_2"
        ], $getRelatedResourceTargetTypesReflection->invokeArgs($translator, []));

        $this->assertEquals([
            "entity", "entity_1", "entity_2"
        ], $getRelatedResourceTargetTypesReflection->invokeArgs($translator, [true]));
    }


    /**
     * Tests getExpectedParameters
     * @return void
     */
    public function testGetExpectedParameters()
    {
        $translator = $this->getQueryTranslator(["getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->once())->method("getRelatedResourceTargetTypes")->willReturn(
            ["entity_1", "entity_2", "entity_3"]
        );

        $getExpectedParametersReflection = $reflection->getMethod("getExpectedParameters");
        $getExpectedParametersReflection->setAccessible(true);

        $expected = $getExpectedParametersReflection->invokeArgs($translator, []);

        $this->assertEquals([
            "fields[entity_1]",
            "fields[entity_2]",
            "fields[entity_3]"
        ], $expected);
    }

    /**
     * Extract parameters not Request
     * @throws ReflectionException
     */
    public function testExtractParametersException()
    {
        $this->expectException(InvalidParameterResourceException::class);

        $translator = $this->getMockForAbstractClass(JsonApiQueryTranslator::class);
        $reflection = new ReflectionClass($translator);

        $extractParametersReflection = $reflection->getMethod("extractParameters");
        $extractParametersReflection->setAccessible(true);

        $extractParametersReflection->invokeArgs($translator, ["foo"]);
    }


    /**
     * Extract parameters not Request
     * @throws ReflectionException
     */
    public function testExtractParameters()
    {
        $translator = $this->getQueryTranslator(["getExpectedParameters"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn(
            ["limit", "filter", "start"]
        );

        $extractParametersReflection = $reflection->getMethod("extractParameters");
        $extractParametersReflection->setAccessible(true);

        $request = new Request([
            "limit" => 0,
            "filter" => json_encode([["property" => "id", "operator" => "in", "value" => ["1", "2", "3"]]]),
            "start" => 3,
            "foo" => "bar"]);

        $extracted = $extractParametersReflection->invokeArgs($translator, [$request]);

        $this->assertEquals([
            "limit" => 0,
            "filter" => json_encode([["property" => "id", "operator" => "in", "value" => ["1", "2", "3"]]]),
            "start" => 3
        ], $extracted);
    }


    /**
     * @return void
     */
    public function testGetDefaultFieldsAndGetFields()
    {
        $fields_l = ["field_a", "field_b"];
        $defaultFields_l = ["subject" => true];

        $fields_r = ["field_c"];
        $defaultFields_r = ["text" => true, "header" => true];

        $translator = $this->getQueryTranslator(["getRelatedResourceTargets"]);
        $reflection = new ReflectionClass($translator);

        $list = new ResourceObjectDescriptionList();

        $resourceObject_l = $this->getResourceObjectDescriptionMock(["getType", "getFields", "getDefaultFields"]);
        $resourceObject_l->expects($this->exactly(4))->method("getType")->willReturn("entity_l");
        $resourceObject_l->expects($this->once())->method("getDefaultFields")->willReturn($defaultFields_l);
        $resourceObject_l->expects($this->once())->method("getFields")->willReturn($fields_l);

        $resourceObject_r = $this->getResourceObjectDescriptionMock(["getType", "getFields",  "getDefaultFields"]);
        $resourceObject_r->expects($this->exactly(2))->method("getType")->willReturn("entity_r");
        $resourceObject_r->expects($this->once())->method("getDefaultFields")->willReturn($defaultFields_r);
        $resourceObject_r->expects($this->once())->method("getFields")->willReturn($fields_r);

        $list[] = $resourceObject_l;
        $list[] = $resourceObject_r;

        $translator->expects($this->any())->method("getRelatedResourceTargets")->with(true)->willReturn($list);

        $getDefaultFieldsReflection = $reflection->getMethod("getDefaultFields");
        $getDefaultFieldsReflection->setAccessible(true);

        $getFieldsReflection = $reflection->getMethod("getFields");
        $getFieldsReflection->setAccessible(true);

        $this->assertEquals($defaultFields_l, $getDefaultFieldsReflection->invokeArgs($translator, ["entity_l"]));
        $this->assertEquals($defaultFields_r, $getDefaultFieldsReflection->invokeArgs($translator, ["entity_r"]));

        $this->assertEquals($fields_l, $getFieldsReflection->invokeArgs($translator, ["entity_l"]));
        $this->assertEquals($fields_r, $getFieldsReflection->invokeArgs($translator, ["entity_r"]));
    }


    /**
     * Tests hasOnlyAllowedFields()
     * @return void
     */
    public function testHasOnlyAllowedFields()
    {

        $translator = $this->getQueryTranslator(["getFields"]);
        $reflection = new ReflectionClass($translator);

        $received = [[
            "limit", "start", "filter", "foo"
        ], [
            "limit", "start", "filter"
        ], [
            "limit", "start"
        ]];

        $fields = [[
            "limit", "start", "filter"
        ], [
            "limit", "start", "filter"
        ], [
            "limit", "start", "filter"
        ]];

        $results = [[
            "foo"
        ], [
        ], [
        ]];

        $translator->expects($this->exactly(count($received)))->method("getFields")->with(
            "entity"
        )->willReturnOnConsecutiveCalls(
            ... $fields
        );

        $hasOnlyAllowedFieldsReflection = $reflection->getMethod("hasOnlyAllowedFields");
        $hasOnlyAllowedFieldsReflection->setAccessible(true);

        foreach ($received as $index => $test) {
            // array_dif returns array with index where diff was found, compare values here
            $this->assertEquals(
                array_values($results[$index]),
                array_values($hasOnlyAllowedFieldsReflection->invokeArgs($translator, [$test, "entity"]))
            );
        }
    }


    /**
     * tests getIncludes()
     * @return void
     */
    public function testGetIncludes()
    {
        $bag = new ParameterBag();

        $translator = $this->getQueryTranslator(["getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->any())->method("getRelatedResourceTargetTypes")->willReturn(["entity_1", "entity_2"]);

        $getIncludesReflection = $reflection->getMethod("getIncludes");
        $getIncludesReflection->setAccessible(true);

        $this->assertSame($bag, $getIncludesReflection->invokeArgs($translator, [$bag]));

        $bag->include = "entity_2,entity_1";
        $this->assertSame($bag, $getIncludesReflection->invokeArgs($translator, [$bag]));

        $bag->include = "entity_2";
        $this->assertSame($bag, $getIncludesReflection->invokeArgs($translator, [$bag]));
    }


    /**
     * tests testGetIncludes() with Exception
     * @return void
     */
    public function testGetIncludesWithException()
    {
        $bag = new ParameterBag();

        $translator = $this->getQueryTranslator(["getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->any())->method("getRelatedResourceTargetTypes")->willReturn(["entity_1", "entity_2"]);

        $getIncludesReflection = $reflection->getMethod("getIncludes");
        $getIncludesReflection->setAccessible(true);

        $this->expectException(InvalidQueryException::class);

        $bag->include = "entity_INVALID";
        $getIncludesReflection->invokeArgs($translator, [$bag]);
    }


    /**
     * tests testGetFieldsets() with exception
     * @return void
     */
    public function testGetFieldsetsWithException()
    {
        $bag = new ParameterBag();

        $translator = $this->getQueryTranslator(["getIncludes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->once())->method("getIncludes")->willThrowException(new InvalidQueryException());

        $getFieldsetsReflection = $reflection->getMethod("getFieldsets");
        $getFieldsetsReflection->setAccessible(true);

        $this->expectException(InvalidQueryException::class);

        $getFieldsetsReflection->invokeArgs($translator, [$bag]);
    }


    /**
     * tests testGetFieldsets()
     * @return void
     */
    public function testGetFieldsets()
    {
        $bag = new ParameterBag();
        $bag->include = "entity_2";
        $bag->{"fields[entity]"} = "";
        $bag->{"fields[entity_2]"} = "";

        $bag2 = new ParameterBag();
        $bag2->include = "entity_2";
        $bag2->{"fields[entity]"} = "";
        $bag2->{"fields[entity_2]"} = "subject,date,to";

        $fields = ["subject", "date"];
        $defaultFields = ["text" => true];
        $fieldOptions = ["html" => ["length" => 200]];

        $translator = $this->getQueryTranslator([
            "parseFields", "parseFieldOptions",
            "mapConfigToFields", "getDefaultFields",
            "getIncludes", "getResourceTarget"
        ]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceObjectDescriptionMock(["getType"]);
        $resourceTarget->expects($this->exactly(2))->method("getType")->willReturn("entity");

        $translator->expects($this->exactly(2))->method("getResourceTarget")->willReturn($resourceTarget);
        $translator->expects($this->exactly(2))->method("getIncludes")->willReturnOnConsecutiveCalls($bag, $bag2);

        $getFieldsetsReflection = $reflection->getMethod("getFieldsets");
        $getFieldsetsReflection->setAccessible(true);


        // test No 1, fieldsets set to ""
        $this->assertSame($bag, $getFieldsetsReflection->invokeArgs($translator, [$bag]));
        $this->assertNull($bag->{"fields[entity]"});
        $this->assertNull($bag->{"fields[entity_2]"});

        $this->assertEquals([], $bag->fields["entity"]);
        $this->assertEquals([], $bag->fields["entity_2"]);


        // test No 2, fieldset entity_2 set
        // new reference for tests, see willReturnOnConsecutiveCalls for getIncludes
        $bag = $bag2;

        $translator->expects($this->once())->method("parseFields")->with($bag, "entity_2")->willReturn($fields);
        $translator->expects($this->once())->method("parseFieldOptions")->with($bag, "entity_2")->willReturn($fieldOptions);
        $translator->expects($this->once())->method("getDefaultFields")->with("entity_2")->willReturn($defaultFields);
        $translator->expects($this->once())->method("mapConfigToFields")->with(
            $fields,
            $fieldOptions,
            $defaultFields,
            "entity_2"
        )->willReturn($fields);

        $this->assertSame($bag, $getFieldsetsReflection->invokeArgs($translator, [$bag]));
        $this->assertNull($bag->{"fields[entity]"});
        $this->assertNull($bag->{"fields[entity_2]"});

        $this->assertEquals([], $bag->fields["entity"]);
        $this->assertEquals($fields, $bag->fields["entity_2"]);
    }


    /**
     * @param ParameterBag $bag
     * @return ResourceQuery
     */
    protected function getResourceQuery(ParameterBag $bag): ResourceQuery
    {
        return new class ($bag) extends ResourceQuery {
        };
    }


    /**
     * @return object
     */
    protected function getParameterResource(): object
    {
        return new class {
            public function getParameters(): array
            {
                return [
                    "foo" => 1,
                    "bar" => 2
                ];
            }
        };
    }


    /**
     *
     * @return QueryTranslator|MockObject
     */
    protected function getQueryTranslator(array $methods = []): MockObject
    {
        return $this->getMockForAbstractClass(
            JsonApiQueryTranslator::class,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }


    protected function getResourceObjectDescriptionMock(array $methods = []): MockObject
    {
        return $this->getMockForAbstractClass(
            ResourceObjectDescription::class,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
