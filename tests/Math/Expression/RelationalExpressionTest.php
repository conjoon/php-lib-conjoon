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

use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\OperatorCallTrait;
use Conjoon\Math\Expression\RelationalExpression;
use Conjoon\Math\Expression\Operator\RelationalOperator;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Operand;
use Conjoon\Math\OperandList;
use Conjoon\Math\Value;
use Conjoon\Math\VariableName;
use Tests\TestCase;

/**
 * Tests RelationalExpression.
 */
class RelationalExpressionTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $uses = class_uses(RelationalExpression::class);
        $this->assertContains(OperatorCallTrait::class, $uses);

        $this->assertSame(RelationalOperator::class, RelationalExpression::getOperatorClass());

        $operator = RelationalOperator::EQ;

        $lftOperand = $this->createMockForAbstract(Operand::class);
        $rtOperand = $this->createMockForAbstract(Operand::class);

        $expression = new RelationalExpression(
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
        $this->expectExceptionMessage("expects 2 operands");

        $operator = RelationalOperator::EQ;
        new RelationalExpression(
            $operator,
            new OperandList()
        );
    }

    /**
     * Tests toString()
     */
    public function testToString()
    {
        $operator = RelationalOperator::EQ;
        $expression = new RelationalExpression(
            $operator,
            OperandList::make(
                new Value(1),
                new Value(2)
            )
        );

        $this->assertSame("(1==2)", $expression->toString());

        $operator = RelationalOperator::LE;
        $lftOp = RelationalOperator::NE;
        $rtOp = RelationalOperator::GT;

        $expression = new RelationalExpression(
            $operator,
            OperandList::make(
                new RelationalExpression(
                    $lftOp,
                    OperandList::make(
                        new VariableName("x"),
                        new VariableName("y")
                    )
                ),
                new RelationalExpression(
                    $rtOp,
                    OperandList::make(
                        new VariableName("a"),
                        new VariableName("b")
                    )
                )
            )
        );


        $this->assertSame(
            "((x!=y)<=(a>b))",
            $expression->toString()
        );
    }
}
