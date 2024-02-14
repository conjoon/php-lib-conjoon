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
use Conjoon\JsonApi\Query\Validation\Parameter\PnFilterRule;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\JsonApi\Query\BaseListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailFolderListQueryValidator;
use Tests\TestCase;

class MailFolderListQueryValidatorTest extends TestCase
{

    public function testClass()
    {
        $validator = $this->getListQueryValidator();
        $this->assertInstanceOf(BaseListQueryValidator::class, $validator);
    }

    public function testGetParameterRules() {

        $validator = $this->getListQueryValidator();
        $rules = $validator->getParameterRules($this->getJsonApiQuery());

        $this->assertInstanceOf(PnFilterRule::class, $rules->peek());
    }


    public function testGetAllowedParameterNames() {

        $validator = $this->getListQueryValidator();
        $names = $validator->getAllowedParameterNames($this->getJsonApiQuery());

        $this->assertEqualsCanonicalizing([
            "filter", "relfield:fields[MailFolder]", "fields[MailFolder]", "options[MailFolder]"
        ], $names);
    }

    public function testValidate() {

        $validator = $this->getListQueryValidator();

        $errors = new ValidationErrors();
        $validator->validate(
            $this->getJsonApiQuery(
                "options[MailFolder]=" . json_encode(["dissolveNamespaces" => ["INBOX", "[GMAIL]"]])
            ), $errors);
        $this->assertSame(0, $errors->count());


    }

    private function getJsonApiQuery(?string $query = null): Query {
        return new Query($query ?? "", new MailFolderDescription());
    }

    private function getListQueryValidator(): MailFolderListQueryValidator {
        return new MailFolderListQueryValidator();
    }
}
