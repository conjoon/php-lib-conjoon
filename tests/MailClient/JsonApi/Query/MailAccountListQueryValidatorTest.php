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


use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\JsonApi\Query\MailAccountListQueryValidator;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use Tests\TestCase;

class MailAccountListQueryValidatorTest extends TestCase
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


    public function testValidate() {

        $validator = $this->getListQueryValidator();

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery("sort=name"), $errors);
        $this->assertSame(1, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery("include=MailAccount&fields[MailAccount]=name"), $errors);
        $this->assertSame(0, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery("relfield:fields[MailAccount]=name,subscriptions"), $errors);
        $this->assertSame(0, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery(
            "fields[MailAccount]=from&relfield:fields[MailAccount]=name,subscriptions"
        ), $errors);
        $this->assertSame(1, $errors->count());

    }



    public function testGetAllowedParameterNames() {

        $validator = $this->getListQueryValidator();
        $names = $validator->getAllowedParameterNames($this->getJsonApiQuery());

        $this->assertEquals([
            "relfield:fields[MailAccount]", "fields[MailAccount]"
        ], $names);
    }



    private function getJsonApiQuery(?string $query = null): Query {
        return new Query($query ?? "", new MailAccountDescription());
    }

    private function getListQueryValidator() {
        return new MailAccountListQueryValidator();
    }
}
