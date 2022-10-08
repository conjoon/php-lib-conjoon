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
use Conjoon\Statement\Expression\RelationalExpression;
use Conjoon\Statement\Expression\Operator\RelationalOperator;
use Conjoon\Statement\InvalidOperandException;
use Conjoon\Statement\Operand;
use Conjoon\Statement\OperandList;
use Conjoon\Statement\Value;
use \Conjoon\Core\Data\StringStrategy;
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
        $operator = RelationalOperator::IS;

        $lftOperand = $this->createMockForAbstract(Operand::class);
        $rtOperand = $this->createMockForAbstract(Operand::class);

        $expression = new RelationalExpression(
            $operator,
            OperandList::make($lftOperand, $rtOperand)
        );

        $this->assertInstanceOf(Expression::class, $expression);

        $this->assertSame($lftOperand, $expression->getOperands()[0]);
        $this->assertSame($rtOperand, $expression->getOperands()[1]);


        $expression = RelationalExpression::make(
            $operator, $lftOperand, $rtOperand
        );

        $this->assertInstanceOf(RelationalExpression::class, $expression);
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

        $operator = RelationalOperator::IS;
        new RelationalExpression(
            $operator,
            new OperandList()
        );
    }

    /**
     * Tests constructor with InvalidOperandException
     */
    public function testToString()
    {
        $operator = RelationalOperator::IS;
        $expression = RelationalExpression::make(
            $operator,
            new Value(1), new Value(2)
        );

        $this->assertSame("(1=2)", $expression->toString());

        $pnStrategy = new class implements StringStrategy {

            public function toString(mixed $target): string
            {
                list($lft, $rt) = $target->getOperands();

                return "(" .implode(" ", [
                    $target->getOperator()->toString(), $lft->toString(), $rt->toString()
                ]) . ")";
            }
        };

        $this->assertSame("(= 1 2)", $expression->toString(new $pnStrategy));


    }
}
