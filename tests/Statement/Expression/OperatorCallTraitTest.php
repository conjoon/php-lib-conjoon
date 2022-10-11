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

namespace Tests\Conjoon\Statement\Expression;

use Conjoon\Statement\Expression\Expression;
use Conjoon\Statement\Expression\OperatorCallTrait;
use Conjoon\Statement\OperandList;
use Conjoon\Statement\Value;
use BadMethodCallException;
use Tests\TestCase;

/**
 * Tests OperatorStringableTraitTest
 */
class OperatorCallTraitTest extends TestCase
{
    /**
     * Tests __callStatic
     */
    public function testTrait()
    {
        $lft = Value::make(1);
        $rt = Value::make(2);
        $result = TestClass::EXAMPLE($lft, $rt);



        $this->assertInstanceOf(TestClass::class, $result);
        $this->assertSame($result->enum, TestEnum::EXAMPLE);
        $this->assertSame($lft, $result->operands[0]);
        $this->assertSame($rt, $result->operands[1]);
    }


    /**
     * Tests __callStatic with target enum not existing
     */
    public function testWithMissingEnum()
    {
        $this->expectException(BadMethodCallException::class);

        $lft = Value::make(1);
        $rt = Value::make(2);
        TestClass::MISSING($lft, $rt);
    }
}

enum TestEnum
{
    case EXAMPLE;
}

class TestClass extends Expression
{
    use OperatorCallTrait;

    public TestEnum $enum;

    public OperandList $operands;

    public function __construct(TestEnum $enum, OperandList $operands)
    {
        $this->enum = $enum;
        $this->operands = $operands;
    }

    public static function getOperatorClass(): string
    {
        return TestEnum::class;
    }
};
