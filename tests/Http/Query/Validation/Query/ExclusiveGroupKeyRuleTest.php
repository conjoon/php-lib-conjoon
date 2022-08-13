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

namespace Tests\Conjoon\Http\Query\Validation\Query;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Http\Query\Validation\Query\ExclusiveGroupKeyRule;
use Conjoon\Http\Query\Validation\Query\QueryRule;
use Tests\TestCase;
use Conjoon\Http\Query\Query;

/**
 * Tests OnlyParameterNamesRule.
 */
class ExclusiveGroupKeyRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
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
    public function testIsValid()
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
        $this->assertStringContainsString(
            "\"MessageItem\" must not appear in group \"relfield:fields\", since it was already " .
            "configured with \"fields\"",
            $errors[0]->getDetails()
        );
        $this->assertTrue($rule->isValid($query, $errors));
    }
}
