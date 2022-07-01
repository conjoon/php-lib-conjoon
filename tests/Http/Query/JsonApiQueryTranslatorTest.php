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
use Conjoon\Http\Query\InvalidQueryParameterValueException;
use Conjoon\Http\Query\JsonApiQueryTranslator;
use Conjoon\Http\Query\QueryTranslator;
use Conjoon\Core\ResourceQuery;
use Conjoon\Http\Query\UnexpectedQueryParameterException;
use Conjoon\Http\Resource\ResourceObjectDescription;
use Conjoon\Http\Resource\ResourceObjectDescriptionList;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionException;
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
     * Tests mapConfigToFields()
     * @return void
     * @throws ReflectionException
     */
    public function testMapConfigToFields(): void
    {
        $translator = $this->getQueryTranslator(["getDefaultFields"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->once())->method("getDefaultFields")->with("entity")->willReturn(["fields"]);


        $mapConfigToFields = $reflection->getMethod("mapConfigToFields");
        $mapConfigToFields->setAccessible(true);

        $this->assertEquals(["fields"], $mapConfigToFields->invokeArgs($translator, [[], [], "entity"]));
    }


    /**
     * Tests extractFieldOptions()
     * @return void
     * @throws ReflectionException
     */
    public function testExtractFieldOptions(): void
    {
        $translator = $this->getQueryTranslator(["extractFieldOptions"]);
        $reflection = new ReflectionClass($translator);

        $mapConfigToFields = $reflection->getMethod("extractFieldOptions");
        $mapConfigToFields->setAccessible(true);

        $this->assertEquals([], $mapConfigToFields->invokeArgs($translator, [new ParameterBag(), "entity"]));
    }


    /**
     * Tests getRelatedResourceTargets
     * @return void
     * @throws ReflectionException
     */
    public function testGetRelatedResourceTargets(): void
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
     * @throws ReflectionException
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
     * @throws ReflectionException
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
            "include",
            "fields[entity_1]",
            "fields[entity_2]",
            "fields[entity_3]"
        ], $expected);
    }


    /**
     * tests validateParameters() with unexpected parameters
     *
     * @param array $parameters
     * @return void
     */
    public function testValidateParametersUnexpectedQueryParameterException()
    {
        $expected = ["fields[MessageItem]", "include", "options"];
        $parameters = ["opt" => "field.1", "fields" => "field.1"];

        $translator = $this->getQueryTranslator(["getExpectedParameters", "getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);
        $validateParametersRefl = $reflection->getMethod("validateParameters");
        $validateParametersRefl->setAccessible(true);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn($expected);

        $this->expectException(UnexpectedQueryParameterException::class);
        $this->expectExceptionMessage("unexpected parameters \"opt\", \"fields\"");

        $validateParametersRefl->invokeArgs($translator, [$parameters]);
    }


    /**
     * tests validateParameters() InvalidQueryParameterValueException due to include
     * containing unknown related resources
     *
     * @param array $parameters
     * @return void
     */
    public function testValidateParametersInvalidQueryParameterValueExceptionInclude()
    {
        $expected = ["fields[MessageItem]", "include", "options"];
        $parameters = ["include" => "Mail"];

        $translator = $this->getQueryTranslator(["getExpectedParameters", "getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);
        $validateParametersRefl = $reflection->getMethod("validateParameters");
        $validateParametersRefl->setAccessible(true);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn($expected);
        $translator->expects($this->once())->method("getRelatedResourceTargetTypes")->willReturn(["MailFolder"]);

        $this->expectException(InvalidQueryParameterValueException::class);
        $this->expectExceptionMessage("parameter \"include\" must only contain one of MailFolder");

        $validateParametersRefl->invokeArgs($translator, [$parameters]);
    }


    /**
     * tests validateParameters() InvalidQueryParameterValueException due to fieldset specified
     * with UNKNOWN type
     *
     * @param array $parameters
     * @return void
     */
    public function testValidateParametersInvalidQueryParameterValueExceptionFieldset()
    {
        $expected = ["include", "options"];
        $parameters = ["include" => "MailAccount", "fields[MailFolder]" => "field1,field2"];

        $translator = $this->getQueryTranslator(["getExpectedParameters", "getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);
        $validateParametersRefl = $reflection->getMethod("validateParameters");
        $validateParametersRefl->setAccessible(true);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn($expected);
        $translator->expects($this->once())->method("getRelatedResourceTargetTypes")->willReturn(["MailAccount"]);

        $this->expectException(InvalidQueryParameterValueException::class);
        $this->expectExceptionMessage("\"MailFolder\" was not recognized as");

        $validateParametersRefl->invokeArgs($translator, [$parameters]);
    }


    /**
     * tests validateParameters() InvalidQueryParameterValueException due to fieldset specified
     * with NOT INCLUDED type
     *
     * @param array $parameters
     * @return void
     */
    public function testValidateParametersInvalidQueryParameterValueExceptionFieldsetNotIncludedType()
    {
        $expected = ["fields[MailAccount]", "include", "options"];
        $parameters = ["fields[MailAccount]" => "field1,field2"];

        $translator = $this->getQueryTranslator(["getExpectedParameters", "getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);
        $validateParametersRefl = $reflection->getMethod("validateParameters");
        $validateParametersRefl->setAccessible(true);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn($expected);
        $translator->expects($this->once())->method("getRelatedResourceTargetTypes")->willReturn(["MailAccount"]);

        $this->expectException(InvalidQueryParameterValueException::class);
        $this->expectExceptionMessage("\"MailAccount\" was not mentioned in");

        $validateParametersRefl->invokeArgs($translator, [$parameters]);
    }


    /**
     * tests validateParameters()
     *
     * @param array $parameters
     * @return void
     */
    public function testValidateParameters()
    {
        $expected = ["fields[MailAccount]", "include", "options"];
        $parameters = ["include" => "MailAccount", "options" => "hidePassword", "fields[MailAccount]" => "field1,field2"];

        $translator = $this->getQueryTranslator(["getExpectedParameters", "getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);
        $validateParametersRefl = $reflection->getMethod("validateParameters");
        $validateParametersRefl->setAccessible(true);

        $translator->expects($this->once())->method("getExpectedParameters")->willReturn($expected);
        $translator->expects($this->once())->method("getRelatedResourceTargetTypes")->willReturn(["MailAccount"]);

        $this->assertSame(
            $parameters,
            $validateParametersRefl->invokeArgs($translator, [$parameters])
        );
    }


    /**
     * Extract parameters not Request
     * @throws ReflectionException
     */
    public function testGetParametersException()
    {
        $this->expectException(InvalidParameterResourceException::class);

        $translator = $this->getMockForAbstractClass(JsonApiQueryTranslator::class);
        $reflection = new ReflectionClass($translator);

        $getParametersReflection = $reflection->getMethod("getParameters");
        $getParametersReflection->setAccessible(true);

        $getParametersReflection->invokeArgs($translator, ["foo"]);
    }


    /**
     * Extract parameters not Request
     * @throws ReflectionException
     */
    public function testGetParameters()
    {
        $translator = $this->getQueryTranslator(["getExpectedParameters"]);
        $reflection = new ReflectionClass($translator);

        $getParametersReflection = $reflection->getMethod("getParameters");
        $getParametersReflection->setAccessible(true);

        $request = new Request([
            "fields" => [
                "MessageItem" => "subject"
            ],
            "fields[MailFolder]" => "unreadMessages",
            "limit" => 0,
            "filter" => json_encode([["property" => "id", "operator" => "in", "value" => ["1", "2", "3"]]]),
            "start" => 3,
            "foo" => "bar"]);

        $extracted = $getParametersReflection->invokeArgs($translator, [$request]);

        $this->assertEquals([
            "fields[MessageItem]" => "subject",
            "fields[MailFolder]" => "unreadMessages",
            "limit" => 0,
            "filter" => json_encode([["property" => "id", "operator" => "in", "value" => ["1", "2", "3"]]]),
            "start" => 3,
            "foo" => "bar",
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
     * tests extractIncludes()
     * @return void
     */
    public function testExtractIncludes()
    {
        $bag = new ParameterBag();

        $translator = $this->getQueryTranslator(["getRelatedResourceTargetTypes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->any())->method("getRelatedResourceTargetTypes")->willReturn(["entity_1", "entity_2"]);

        $extractIncludesReflection = $reflection->getMethod("extractIncludes");
        $extractIncludesReflection->setAccessible(true);

        $this->assertSame($bag, $extractIncludesReflection->invokeArgs($translator, [$bag]));

        $bag->include = "entity_2,entity_1";
        $this->assertSame($bag, $extractIncludesReflection->invokeArgs($translator, [$bag]));
        $this->assertEquals(["entity_2", "entity_1"], $bag->include);

        $bag->include = "entity_2";
        $this->assertSame($bag, $extractIncludesReflection->invokeArgs($translator, [$bag]));
        $this->assertEquals(["entity_2"], $bag->include);

        $this->assertSame($bag, $extractIncludesReflection->invokeArgs($translator, [$bag]));
        $this->assertEquals(["entity_2"], $bag->include);
    }


    /**
     * tests testGetFieldsets() with exception
     * @return void
     */
    public function testGetFieldsetsWithException()
    {
        $bag = new ParameterBag();

        $translator = $this->getQueryTranslator(["extractIncludes"]);
        $reflection = new ReflectionClass($translator);

        $translator->expects($this->once())->method("extractIncludes")->willThrowException(new InvalidQueryException());

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
        $fields = [
            "entity" => ["date"],
            "entity_2" => ["subject"]
        ];

        $defaultFields = [
            "entity" => ["date" => true],
            "entity_2" => ["subject" => true]
        ];

        $bag = new ParameterBag();
        $bag->{"fields[entity]"} = "";
        $bag->include = "entity_2";
        $bag->{"fields[entity_2]"} = "";


        $bag2 = new ParameterBag();
        $bag2->include = "entity_2";
        $bag2->{"fields[entity_2]"} = "subject,date,to";


        $translator = $this->getQueryTranslator([
            "parseFields", "extractFieldOptions",
            "mapConfigToFields", "getDefaultFields",
            "extractIncludes", "getResourceTarget"
        ]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceObjectDescriptionMock(["getType"]);
        $resourceTarget->expects($this->exactly(2))->method("getType")->willReturn("entity");

        $translator->expects($this->exactly(2))->method("getResourceTarget")->willReturn($resourceTarget);
        $translator->expects($this->exactly(2))->method("extractIncludes")->willReturnOnConsecutiveCalls(
            $this->returnCallback(function () use ($bag) {
                $bag->include = explode(",", $bag->include);
                return $bag;
            }),
            $this->returnCallback(function () use ($bag2) {
                $bag2->include = explode(",", $bag2->include);
                return $bag2;
            }),
        );

        $translator->expects($this->exactly(4))->method("parseFields")->withConsecutive(
            [$bag->{"fields[entity]"}, "entity"],
            [$bag->{"fields[entity_2]"}, "entity_2"],
            [$bag2->{"fields[entity]"}, "entity"],
            [$bag2->{"fields[entity_2]"}, "entity_2"],
        )->willReturnOnConsecutiveCalls(
            [],
            [],
            $fields["entity"],
            $fields["entity_2"]
        );
        $translator->expects($this->exactly(4))->method("extractFieldOptions")->withConsecutive(
            [$bag, "entity"],
            [$bag, "entity_2"],
            [$bag2, "entity"],
            [$bag2, "entity_2"]
        );

        $translator->expects($this->exactly(4))->method("mapConfigToFields")->withConsecutive(
            [
                [],
                [],
                "entity"
            ],
            [
                [],
                [],
                "entity_2"
            ],
            [
                $fields["entity"],
                [],
                "entity"
            ],
            [
                $fields["entity_2"],
                [],
                "entity_2"
            ]
        )->willReturnOnConsecutiveCalls([], [], $defaultFields["entity"], $defaultFields["entity_2"]);


        $getFieldsetsReflection = $reflection->getMethod("getFieldsets");
        $getFieldsetsReflection->setAccessible(true);


        // test No 1, fieldsets set to ""
        $this->assertSame($bag, $getFieldsetsReflection->invokeArgs($translator, [$bag]));
        $this->assertNull($bag->{"fields[entity]"});
        $this->assertNull($bag->{"fields[entity_2]"});

        $this->assertEquals([], $bag->fields["entity"]);
        $this->assertEquals([], $bag->fields["entity_2"]);


        // test No 2, fieldset entity_2 set
        $this->assertSame($bag2, $getFieldsetsReflection->invokeArgs($translator, [$bag2]));
        $this->assertNull($bag2->{"fields[entity]"});
        $this->assertNull($bag2->{"fields[entity_2]"});

        $this->assertEquals($defaultFields["entity"], $bag2->fields["entity"]);
        $this->assertEquals($defaultFields["entity_2"], $bag2->fields["entity_2"]);
    }


    /**
     * tests parseFields() with [null, $type]
     */
    public function testParseFieldsQueryParamaterNotSet()
    {
        $type = "entity";
        $fields = ["subject", "date"];
        $bag = new ParameterBag();

        list(
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
            ) = $this->setupParseFieldTest();

        $translator->expects($this->once())->method("getFields")->with($type)->willReturn($fields);
        $translator->expects($this->never())->method("hasOnlyAllowedFields");

        $this->assertEquals($fields, $parseFieldsReflection->invokeArgs($translator, [$bag->{"fields[entity]"}, $type]));
    }


    /**
     * tests parseFields() with ["", $type]
     */
    public function testParseFieldsQueryParamaterSetToEmptyString()
    {
        $type = "entity";
        $bag = new ParameterBag();
        $bag->{"fields[entity]"} = "";

        list(
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
            ) = $this->setupParseFieldTest();

        $translator->expects($this->never())->method("getFields");
        $translator->expects($this->never())->method("hasOnlyAllowedFields");

        $this->assertEquals([], $parseFieldsReflection->invokeArgs($translator, [$bag->{"fields[entity]"}, $type]));
    }


    /**
     * tests parseFields() with ["subject", $type]
     */
    public function testParseFieldsWithAllowedField()
    {
        $type = "entity";
        $bag = new ParameterBag();
        $bag->{"fields[entity]"} = "subject";

        list(
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
            ) = $this->setupParseFieldTest();

        $translator->expects($this->never())->method("getFields");
        $translator->expects($this->once())->method("hasOnlyAllowedFields")->with(["subject"], "entity")->willReturn([]);

        $this->assertEquals(["subject"], $parseFieldsReflection->invokeArgs($translator, [$bag->{"fields[entity]"}, $type]));
    }


    /**
     * tests parseFields() with ["*,subject", $type]
     */
    public function testParseFieldsWithWildcard()
    {
        $type = "entity";
        $fields = ["subject", "date"];
        $bag = new ParameterBag();
        $bag->{"fields[entity]"} = "*,subject";

        list(
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
            ) = $this->setupParseFieldTest();

        $translator->expects($this->once())->method("getFields")->with("entity")->willReturn($fields);
        $translator->expects($this->never())->method("hasOnlyAllowedFields");

        $this->assertEquals(["date"], $parseFieldsReflection->invokeArgs($translator, [$bag->{"fields[entity]"}, $type]));
    }


    /**
     * tests parseFields() with exception
     */
    public function testParseFieldsWithException()
    {
        $type = "entity";
        $bag = new ParameterBag();
        $bag->{"fields[entity]"} = "textHtml,subject";

        list(
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
            ) = $this->setupParseFieldTest();

        $translator->expects($this->never())->method("getFields");
        $translator->expects($this->once())->method("hasOnlyAllowedFields")->with(["textHtml", "subject"], "entity")->willReturn(["textHtml"]);

        $this->expectException(InvalidQueryParameterValueException::class);
        $parseFieldsReflection->invokeArgs($translator, [$bag->{"fields[entity]"}, $type]);
    }

    /**
     * @return array
     */
    protected function setupParseFieldTest(): array
    {
        $translator = $this->getQueryTranslator(["getFields", "hasOnlyAllowedFields"]);
        $reflection = new ReflectionClass($translator);

        $parseFieldsReflection = $reflection->getMethod("parseFields");
        $parseFieldsReflection->setAccessible(true);

        return [
            "translator"            => $translator,
            "parseFieldsReflection" => $parseFieldsReflection
        ];
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
