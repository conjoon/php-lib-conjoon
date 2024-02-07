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
            ->withConsecutive(["include"], ["sort"])
            ->willReturnOnConsecutiveCalls(new Parameter("include", ""), $sortParameter);

        $validator = $this->createMockForAbstract(CollectionQueryValidator::class, ["getAvailableSortFields"]);
        $validator->expects($this->once())->method("getAvailableSortFields")->willReturn($sort);

        $rules = $validator->getParameterRules($query);

        $this->assertInstanceOf(ValuesInWhitelistRule::class, $rules->peek());
        $this->assertSame($sort, $rules->peek()->getWhitelist());
    }
}
