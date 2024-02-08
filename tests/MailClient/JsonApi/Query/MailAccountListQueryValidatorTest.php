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
use Conjoon\JsonApi\Query\Query;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\JsonApi\Query\BaseListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailAccountListQueryValidator;
use Tests\TestCase;

class MailAccountListQueryValidatorTest extends TestCase
{

    public function testClass()
    {
        $validator = $this->getListQueryValidator();
        $this->assertInstanceOf(BaseListQueryValidator::class, $validator);
    }


    public function testValidate() {

        $validator = $this->getListQueryValidator();

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery("sort=name"), $errors);
        $this->assertSame(1, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate($this->getJsonApiQuery("include=MailAccount&fields[MailAccount]=name"), $errors);
        $this->assertSame(1, $errors->count());



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

        $this->assertEqualsCanonicalizing([
            "relfield:fields[MailAccount]", "fields[MailAccount]"
        ], $names);
    }



    private function getJsonApiQuery(?string $query = null): Query {
        return new Query($query ?? "", new MailAccountDescription());
    }

    private function getListQueryValidator(): MailAccountListQueryValidator {
        return new MailAccountListQueryValidator();
    }
}
