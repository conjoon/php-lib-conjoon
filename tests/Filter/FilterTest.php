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

namespace Tests\Conjoon\Filter;

use Conjoon\Core\Contract\Stringable;
use Conjoon\Filter\Filter;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\RelationalExpression;
use Conjoon\Math\Value;
use Tests\TestCase;

/**
 * Tests Filter.
 */
class FilterTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $expr = RelationalExpression::EQ(Value::make(1), Value::make(2));
        $filter = new Filter($expr);
        $this->assertInstanceOf(Stringable::class, $filter);
        $this->assertSame($expr, $filter->getExpression());
    }


    /**
     * Tests toString()
     */
    public function testToString()
    {
        $expr = $this->createMockForAbstract(Expression::class, ["toString"]);
        $expr->expects($this->once())->method("toString")->willReturn("string");

        $filter = new Filter($expr);
        $this->assertSame("string", $filter->toString());
    }
}
