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

namespace Tests\Conjoon\Core\Data\Filter\Operation;

use Conjoon\Statement\Expression\Expression;
use Conjoon\Statement\Expression\OperatorCallTrait;
use Conjoon\Statement\Expression\FunctionalExpression;
use Conjoon\Statement\Expression\Operator\FunctionalOperator;
use Conjoon\Statement\InvalidOperandException;
use Conjoon\Statement\Operand;
use Conjoon\Statement\OperandList;
use Conjoon\Statement\Value;
use Conjoon\Statement\VariableName;
use Tests\TestCase;

/**
 * Tests FunctionalExpression.
 */
class FunctionalExpressionTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $uses = class_uses(FunctionalExpression::class);
        $this->assertContains(OperatorCallTrait::class, $uses);

        $this->assertSame(FunctionalOperator::class, FunctionalExpression::getOperatorClass());

        $operator = FunctionalOperator::IN;

        $lftOperand = $this->createMockForAbstract(Operand::class);
        $rtOperand = $this->createMockForAbstract(Operand::class);

        $expression = new FunctionalExpression(
            $operator,
            OperandList::make($lftOperand, $rtOperand)
        );

        $this->assertInstanceOf(Expression::class, $expression);

        $this->assertSame($lftOperand, $expression->getOperands()[0]);
        $this->assertSame($rtOperand, $expression->getOperands()[1]);
    }


    /**
     * Tests constructor with InvalidOperandException
     */
    public function testConstructorWithInvalidOperandException()
    {
        $this->expectException(InvalidOperandException::class);
        $this->expectExceptionMessage("expects at least 2 operands");

        $operator = FunctionalOperator::IN;
        new FunctionalExpression(
            $operator,
            new OperandList()
        );
    }

    /**
     * Tests toString()
     */
    public function testToString()
    {
        $operator = FunctionalOperator::IN;
        $operands = OperandList::make(
            VariableName::make("seen"),
            Value::make(1),
            Value::make(2),
            Value::make(3),

        );
        $expression = new FunctionalExpression(
            $operator,
            $operands
        );

        $this->assertSame("(seen IN (1, 2, 3))", $expression->toString());
    }
}
