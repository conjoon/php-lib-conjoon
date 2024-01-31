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

namespace Tests\Conjoon\JsonApi\Query\Validation;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\Rule\ValuesInWhitelistRule;
use Tests\TestCase;

/**
 * Tests CollectionValidator.
 */
class CollectionValidatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $validator = new CollectionQueryValidator();
        $this->assertInstanceOf(QueryValidator::class, $validator);
    }

    /**
     * tests getAvailableFields
     */
    public function testGetAvailableFields()
    {
        $validator = new CollectionQueryValidator();
        $getAvailableFields = $this->makeAccessible($validator, "getAvailableFields");
        $resourceTarget = $this->createMockForAbstract(
            ResourceDescription::class,
            [
                "getType", "getFields", "getAllRelationshipResourceDescriptions"
            ]
        );

        $resourceTargetChild = $this->createMockForAbstract(
            ResourceDescription::class,
            ["getFields", "getType"]
        );
        $resourceTarget->expects($this->once())->method("getType")->willReturn("MessageItem");
        $resourceTarget->expects($this->exactly(2))->method("getFields")->willReturn(
            ["subject", "date", "size"]
        );

        $resourceTargetChild->expects($this->once())->method("getType")->willReturn("MailFolder");
        $resourceTargetChild->expects($this->once())->method("getFields")->willReturn(
            ["unreadMessages", "totalMessages"]
        );

        $resourceTargetList = new ResourceDescriptionList();
        $resourceTargetList[] = $resourceTarget;
        $resourceTargetList[] = $resourceTargetChild;

        $resourceTarget->expects($this->once())
            ->method("getAllRelationshipResourceDescriptions")
            ->with(true)
            ->willReturn($resourceTargetList);


        $fields = [
            "subject", "date", "size",
            "MessageItem.subject", "MessageItem.date", "MessageItem.size",
            "MailFolder.unreadMessages", "MailFolder.totalMessages"
        ];

        $this->assertEqualsCanonicalizing(
            $fields,
            $getAvailableFields->invokeArgs($validator, [$resourceTarget])
        );
    }


    /**
     * tests getAvailableSortFields
     */
    public function testGetAvailableSortFields()
    {
        $fields = [
            "subject", "date", "size",
            "MessageItem.subject", "MessageItem.date", "MessageItem.size",
           "MailFolder.unreadMessages", "MailFolder.totalMessages"
        ];

        $sortFields = array_merge($fields, array_map(fn ($field) => "-" . $field, $fields));

        $validator = $this->getMockBuilder(CollectionQueryValidator::class)
                          ->disableOriginalConstructor()
                          ->onlyMethods(["getAvailableFields"])
                          ->getMock();

        $getAvailableSortFields = $this->makeAccessible($validator, "getAvailableSortFields");
        $validator->expects($this->once())->method("getAvailableFields")->willReturn(
            $fields
        );

        $resourceTarget = $this->createMockForAbstract(ResourceDescription::class);

        $this->assertEqualsCanonicalizing(
            $sortFields,
            $getAvailableSortFields->invokeArgs($validator, [$resourceTarget])
        );
    }


    /**
     * test getAllowedParameterNames()
     */
    public function getAllowedParameterNames()
    {
        $validator = new CollectionQueryValidator();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceDescription"])->getMock();

        $resourceTarget = $this->createMockForAbstract(ResourceDescription::class, [
        ]);
        $query->expects($this->any())->method("getResourceDescription")->willReturn($resourceTarget);


        $this->assertContains(
            "sort",
            $validator->getAllowedParameterNames($query)
        );
    }


    /**
     * test getParameterRules()
     */
    public function testGetParameterRules()
    {
        $sort = ["date", "subject", "size"];
        $sortParameter = new Parameter("sort", "-date,subject");
        $ResourceDescriptionList = new ResourceDescriptionList();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getParameter", "getResourceDescription"])
            ->getMock();

        $resourceTarget = $this->createMockForAbstract(
            ResourceDescription::class,
            ["getAllRelationshipPaths", "getAllRelationshipResourceDescriptions"]
        );

        $query->expects($this->any())->method("getResourceDescription")->willReturn($resourceTarget);
        $query->expects($this->any())
            ->method("getParameter")
            ->withConsecutive(["sort"], ["include"])
            ->willReturnOnConsecutiveCalls($sortParameter, new Parameter("include", ""));

        $validator = $this->createMockForAbstract(CollectionQueryValidator::class, ["getAvailableSortFields"]);
        $validator->expects($this->once())->method("getAvailableSortFields")->willReturn($sort);

        $rules = $validator->getParameterRules($query);

        $this->assertInstanceOf(ValuesInWhitelistRule::class, $rules->peek());
        $this->assertSame($sort, $rules->peek()->getWhitelist());
    }
}
