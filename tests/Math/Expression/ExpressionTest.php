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

namespace Tests\Conjoon\Math\Expression;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Math\InvalidOperationException;
use Conjoon\Math\Operand;
use Conjoon\Math\OperandList;
use Tests\JsonableTestTrait;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests FunctionalOperator.
 */
class ExpressionTest extends TestCase
{
    use StringableTestTrait;
    use JsonableTestTrait;

// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $expression = $this->createMockForAbstract(Expression::class);
        $this->assertInstanceOf(Stringable::class, $expression);
        $this->assertInstanceOf(Arrayable::class, $expression);

        $operands = $this->makeAccessible($expression, "operands", true);
        $list = new OperandList();
        $operands->setValue($expression, $list);

        $operator = $this->makeAccessible($expression, "operator", true);
        $value = $this->createMockForAbstract(Operator::class);
        $operator->setValue($expression, $value);


        $this->assertSame($value, $expression->getOperator());
    }

    /**
     * @return void
     */
    public function testToStringAndToArray(): void
    {
        $operator = $this->createMockForAbstract(Operator::class, ["toString"]);
        $operand1 = $this->createMockForAbstract(Operand::class, ["toArray"]);
        $operand2 = $this->createMockForAbstract(Operand::class, ["toArray"]);

        $operator->expects($this->any())->method("toString")->willReturn("+");
        $operand1->expects($this->any())->method("toString")->willReturn("A");
        $operand2->expects($this->any())->method("toString")->willReturn("B");
        $operandList = OperandList::make($operand1, $operand2);

        $expression = $this->createMockForAbstract(Expression::class, ["getOperator", "getOperands"]);
        $expression->expects($this->exactly(2))->method("getOperator")->willReturn($operator);
        $expression->expects($this->exactly(2))->method("getOperands")->willReturn($operandList);

        $this->assertSame("(A+B)", $expression->toString());
        $this->assertSame(["+", "A", "B"], $expression->toArray());

        $operandList = OperandList::make($operand1);
        $expression = $this->createMockForAbstract(Expression::class, ["getOperator", "getOperands"]);
        $expression->expects($this->exactly(1))->method("getOperator")->willReturn($operator);
        $expression->expects($this->exactly(1))->method("getOperands")->willReturn($operandList);
        $this->assertSame("(+A)", $expression->toString());

        $this->runToStringTest(Expression::class);
    }


    /**
     * @return void
     */
    public function testToJson(): void
    {
        $expression = $this->createMockForAbstract(Expression::class, ["getOperands", "getOperator"]);

        $expression->expects($this->any())->method("getOperands")->willReturn(new OperandList());
        $expression->expects($this->any())->method("getOperator")->willReturn(
            $this->createMockForAbstract(Operator::class)
        );

        $this->runToJsonTest($expression);
    }


    /**
     * tests getValue()
     * @return void
     */
    public function testGetValue(): void
    {
        $this->expectException(InvalidOperationException::class);

        $expression = $this->createMockForAbstract(Expression::class);
        $expression->getValue();
    }
}
