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

namespace Tests\Conjoon\JsonApi;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Core\Validation\Rule as ValidationRule;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\JsonApi\Query as JsonApiQuery;
use Conjoon\JsonApi\Validation\Rule;
use stdClass;
use Tests\TestCase;

/**
 * Tests JsonApi's Rule implementation.
 */
class RuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = $this->createMockForAbstract(Rule::class);
        $this->assertInstanceOf(ValidationRule::class, $rule);
    }

    /**
     * test isValid() throwing UnexpectedTypeException
     */
    public function testisValidWithUnexpectedTypeException()
    {
        $rule = $this->createMockForAbstract(Rule::class);
        $this->expectException(UnexpectedTypeException::class);

        $rule->isValid(new stdClass(), new ValidationErrors());
    }

    /**
     * test isValid() delegating to validate
     */
    public function testIsValid()
    {
        $query = $this->getMockBuilder(JsonApiQuery::class)->disableOriginalConstructor()->getMock();
        $errors = new ValidationErrors();

        $rule = $this->createMockForAbstract(Rule::class, ["validate"]);
        $rule->expects($this->once())->method("validate")->with($query, $errors)->willReturn(true);

        $this->assertSame(true, $rule->isValid($query, $errors));
    }
}