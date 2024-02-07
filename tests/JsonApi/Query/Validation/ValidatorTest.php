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
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\JsonApi\Query\Validation\Parameter\IncludeRule;
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Exception\UnexpectedQueryParameterException;
use Conjoon\Web\Validation\Query\Rule\OnlyParameterNamesRule;
use Conjoon\Web\Validation\Query\Rule\RequiredParameterNamesRule;
use Conjoon\Web\Validation\QueryValidator as HttpQueryValidator;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests Validator.
 */
class ValidatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $validator = new QueryValidator();
        $this->assertInstanceOf(HttpQueryValidator::class, $validator);
    }

    /**
     * tests unfold()
     * @throws ReflectionException
     */
    public function testUnfoldWithUnexpectedQueryParameterException()
    {
        $validator = new QueryValidator();
        $unfold = $this->makeAccessible($validator, "unfoldInclude");
        $this->expectException(UnexpectedQueryParameterException::class);

        $parameter = new Parameter("parameter", "");
        $unfold->invokeArgs($validator, [$parameter]);
    }


    /**
     * tests unfold()
     */
    public function testUnfold()
    {
        $validator = new QueryValidator();
        $unfold = $this->makeAccessible($validator, "unfoldInclude");
        $parameter = new Parameter("include", "MailFolder.MailAccount,MailFolder.MailAccount.Server,MailFolder");

        $this->assertEquals(
            ["MailFolder", "MailAccount", "Server"],
            $unfold->invokeArgs($validator, [$parameter])
        );

        $parameter = new Parameter("include", "");

        $this->assertEquals(
            [],
            $unfold->invokeArgs($validator, [$parameter])
        );
    }


    /**
     * test getAllowedParameterNames()
     */
    public function testGetAllowedParameterNames()
    {
        $validator = new QueryValidator();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceDescription"])->getMock();

        $resourceTarget = $this->createMockForAbstract(ResourceDescription::class, [
            "getType", "getAllRelationshipPaths"]);
        $query->expects($this->once())->method("getResourceDescription")->willReturn($resourceTarget);

        $resourceTarget->expects($this->once())->method("getAllRelationshipPaths")->with(true)->willReturn([
            "path", "entity", "path.entity2", "path.entity2.entity3"
        ]);


        $this->assertEqualsCanonicalizing([
            "include",
            "fields[entity]",
            "fields[path]",
            "fields[entity2]",
            "fields[entity3]"
        ], $validator->getAllowedParameterNames($query));
    }


    /**
     * test getRequiredParameterNames()
     */
    public function testGetRequiredParameterNames()
    {
        $validator = new QueryValidator();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->assertEquals(
            [],
            $validator->getRequiredParameterNames($query)
        );
    }


    /**
     * test supports()
     */
    public function testSupports()
    {
        $validator = new QueryValidator();

        $this->assertTrue(
            $validator->supports(
                $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock()
            )
        );


        $this->assertFalse(
            $validator->supports(
                $this->getMockBuilder(HttpQuery::class)->disableOriginalConstructor()->getMock()
            )
        );
    }


    /**
     * test getQueryRules()
     */
    public function testGetQueryRules()
    {
        $allowedParameterNames = ["include"];
        $requiredParameterNames = ["include"];

        $validator = $this->getMockBuilder(QueryValidator::class)
                          ->onlyMethods(["getAllowedParameterNames", "getRequiredParameterNames"])
                          ->getMock();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()->getMock();

        $validator->expects($this->once())->method("getAllowedParameterNames")
                  ->with($query)
                  ->willReturn($allowedParameterNames);

        $validator->expects($this->once())->method("getRequiredParameterNames")
            ->with($query)
            ->willReturn($requiredParameterNames);


        $rules = $validator->getQueryRules($query);

        $this->assertSame(2, count($rules));

        $this->assertInstanceOf(OnlyParameterNamesRule::class, $rules[0]);
        $this->assertSame($allowedParameterNames, $rules[0]->getWhitelist());

        $this->assertInstanceOf(RequiredParameterNamesRule::class, $rules[1]);
        $this->assertSame($requiredParameterNames, $rules[1]->getRequired());
    }


    /**
     * test getParameterRules()
     */
    public function testGetParameterRules()
    {
        $includes = ["OwningResourceEntity", "MessageItem", "MailFolder"];
        $includeParameter = new Parameter("include", "MessageItem,MailFolder");
        $whitelist = ["fields[MessageItem]"];
        $ResourceDescriptionList = new ResourceDescriptionList();

        $query = $this->getMockBuilder(Query::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(["getParameter", "getResourceDescription"])
                      ->getMock();

        $resourceTarget = $this->createMockForAbstract(
            ResourceDescription::class,
            ["getType", "getAllRelationshipPaths", "getAllRelationshipResourceDescriptions"]
        );
        $resourceTarget->method("getType")->willReturn("OwningResourceEntity");

        $query->expects($this->any())->method("getResourceDescription")->willReturn($resourceTarget);
        $query->expects($this->any())
              ->method("getParameter")
              ->withConsecutive(["include"])
              ->willReturnOnConsecutiveCalls($includeParameter);

        $resourceTarget->expects($this->any())->method("getAllRelationshipPaths")->willReturn(
            $whitelist
        );

        $resourceTarget->expects($this->once())
                       ->method("getAllRelationshipResourceDescriptions")
                       ->with(true)
                       ->willReturn($ResourceDescriptionList);

        $validator = $this->createMockForAbstract(QueryValidator::class, ["isAllowedParameterName", "getAvailableSortFields"]);
        $validator->expects($this->any())->method("isAllowedParameterName")->willReturn(true);

        $rules = $validator->getParameterRules($query);

        $this->assertSame(2, count($rules));
        $this->assertInstanceOf(IncludeRule::class, $rules[0]);
        $this->assertInstanceOf(FieldsetRule::class, $rules[1]);
        $this->assertSame($whitelist, $rules[0]->getWhitelist());
        $this->assertSame($ResourceDescriptionList, $rules[1]->getResourceDescriptionList());
        $this->assertSame($includes, $rules[1]->getIncludes());
    }
}
