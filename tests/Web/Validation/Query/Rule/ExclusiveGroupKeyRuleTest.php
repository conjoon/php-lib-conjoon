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

namespace Conjoon\Web\Validation\Query\Rule\Query;

use Conjoon\Data\ParameterList;
use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Query\QueryRule;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests OnlyParameterNamesRule.
 */
class ExclusiveGroupKeyRuleTest extends TestCase
{
    /**
     * Class functionality
     * @throws ReflectionException
     */
    public function testClass(): void
    {
        $cGroups = ["fields", "relfield:fields"];

        $rule = new ExclusiveGroupKeyRule($cGroups);
        $this->assertInstanceOf(QueryRule::class, $rule);

        $groups = $this->makeAccessible($rule, "groups", true);

        $this->assertEquals(
            $cGroups,
            $groups->getValue($rule)
        );
    }


    /**
     * Test validation for this rule.
     *
     * @return void
     */
    public function testIsValid(): void
    {
        $parameterListValid = new ParameterList();
        $parameterListValid[] = new Parameter("fields[MessageItem]", "subject");
        $parameterListValid[] = new Parameter("relfield:fields[MailFolder]", "+previewText");

        $parameterListInvalid = new ParameterList();
        $parameterListInvalid[] = new Parameter("fields[MessageItem]", "subject");
        $parameterListInvalid[] = new Parameter("relfield:fields[MessageItem]", "+previewText");

        $query = $this->createMockForAbstract(Query::class, ["getAllParameters"]);
        $query->expects($this->exactly(2))
            ->method("getAllParameters")
            ->willReturnOnConsecutiveCalls(
                $parameterListInvalid,
                $parameterListValid
            );

        $cGroups = ["fields", "relfield:fields"];

        $rule = new ExclusiveGroupKeyRule($cGroups);

        $errors = new ValidationErrors();
        $this->assertFalse($rule->isValid($query, $errors));

        /**
         * @var ValidationError $err
         */
        $err = $errors[0];

        $this->assertStringContainsString(
            "\"MessageItem\" must not appear in group \"relfield:fields\", since it was already " .
            "configured with \"fields\"",
            $err->getDetails()
        );
        $this->assertTrue($rule->isValid($query, $errors));
    }
}
