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

namespace Tests\Conjoon\MailClient\JsonApi\Query;


use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\JsonApi\Query\BaseListQueryValidator;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use Tests\TestCase;

class BaseListQueryValidatorTest extends TestCase
{

    public function testClass()
    {
        $validator = $this->getListQueryValidator();
        $this->assertInstanceOf(CollectionQueryValidator::class, $validator);
    }

    public function testGetParameterRules() {

        $validator = $this->getListQueryValidator();
        $rules = $validator->getParameterRules($this->getJsonApiQuery());

        $this->assertInstanceOf(RelfieldRule::class, $rules->peek());
    }

    public function testGetQueryRules() {

        $validator = $this->getListQueryValidator();
        $rules = $validator->getQueryRules($this->getJsonApiQuery());

        $groupKeyRule = $rules->peek();
        $this->assertInstanceOf(ExclusiveGroupKeyRule::class, $groupKeyRule);
        $this->assertEquals(["fields", "relfield:fields"], $groupKeyRule->getGroups());
    }


    public function testGetAllowedParameterNames() {

        $validator = $this->getListQueryValidator();
        $names = $validator->getAllowedParameterNames($this->getJsonApiQuery());

        $this->assertEqualsCanonicalizing([
            "relfield:fields[rd]", "fields[rd]", "include", "sort"
        ], $names);
    }



    private function getJsonApiQuery(?string $query = null): Query {
        $rd = $this->getMockForAbstractClass(ResourceDescription::class);
        $rd->expects($this->any())->method("getType")->willReturn("rd");
        return new Query($query ?? "", $rd);
    }

    private function getListQueryValidator() {
        return $this->createMockForAbstract(BaseListQueryValidator::class);
    }
}
