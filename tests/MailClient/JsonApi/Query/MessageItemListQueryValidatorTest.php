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
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\JsonApi\Query\BaseListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailFolderListQueryValidator;
use Conjoon\Web\Validation\Parameter\Rule\IntegerValueRule;
use Conjoon\Web\Validation\Parameter\Rule\JsonEncodedRule;
use Conjoon\MailClient\JsonApi\Query\MessageItemListQueryValidator;
use Tests\TestCase;

class MessageItemListQueryValidatorTest extends TestCase
{

    public function testClass()
    {
        $validator = $this->getListQueryValidator();
        $this->assertInstanceOf(BaseListQueryValidator::class, $validator);
    }

    public function testGetAllowedParameterNames() {

        $validator = $this->getListQueryValidator();
        $names = $validator->getAllowedParameterNames($this->getJsonApiQuery());

        $this->assertEqualsCanonicalizing([
            "fields[MessageItem]",
            "relfield:fields[MessageItem]",
            "relfield:fields[MessageBody]",
            "fields[MessageBody]",
            "sort",
            "include",
            "page[start]",
            "page[limit]",
            "options[MessageBody]"
        ], $names);
    }

    public function testGetRequiredParameterNames() {
        $validator = $this->getListQueryValidator();

        $this->assertEqualsCanonicalizing([
            "page[start]", "page[limit]"
        ], $validator->getRequiredParameterNames($this->getJsonApiQuery()));
    }


    public function testGetParameterRules() {

        $validator = $this->getListQueryValidator();
        $rules = $validator->getParameterRules($this->getJsonApiQuery());

        $this->assertNotNull(
            $rules->findBy(fn($rule) => ($rule instanceof JsonEncodedRule) &&
                $rule->getParameterName() === "options[MessageBody]"
            )
        );
        $this->assertNotNull($rules->findBy(
            fn($rule) => ($rule instanceof IntegerValueRule) &&
                $rule->getParameterName() === "page[start]"
        ));
        $this->assertNotNull($rules->findBy(
            fn($rule) => ($rule instanceof IntegerValueRule) &&
                $rule->getParameterName() === "page[limit]"
        ));
    }


    public function testValidate() {

        $validator = $this->getListQueryValidator();

        $pageString = "page[start]=100&page[limit]=100";

        $errors = new ValidationErrors();
        $validator->validate(
            $this->getJsonApiQuery(
                "{$pageString}&fields[MessageItem]="
            ), $errors);
        $this->assertSame(0, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate(
            $this->getJsonApiQuery(
                "{$pageString}&options[MessageBody]=" . json_encode(["textHtml" => ["length" => 200]])
            ), $errors);
        $this->assertSame(0, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate(
            $this->getJsonApiQuery(
                "page[start]=100&page[limit]=100"
            ), $errors);
        $this->assertSame(0, $errors->count());

        $errors = new ValidationErrors();
        $validator->validate(
            $this->getJsonApiQuery(
                "page[start]=100&page[limit]=-100"
            ), $errors);
        $this->assertSame(1, $errors->count());
        $this->assertStringContainsString(
            "parameter \"page[limit]\"'s value \"-100\" is not >= 1",
            $errors[0]->getDetails()
        );

    }

    private function getJsonApiQuery(?string $query = null): Query {
        return new Query($query ?? "", new MessageItemDescription());
    }

    private function getListQueryValidator(): MessageItemListQueryValidator {
        return new MessageItemListQueryValidator();
    }
}
