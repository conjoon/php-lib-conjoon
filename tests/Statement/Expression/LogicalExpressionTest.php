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

namespace Tests\Conjoon\Statement\Expression\Expression;

use Conjoon\Statement\Expression\Expression;
use Conjoon\Statement\Expression\LogicalExpression;
use Conjoon\Statement\Expression\OperatorCallTrait;
use Conjoon\Statement\InvalidOperandException;
use Conjoon\Statement\Operand;
use Conjoon\Statement\Expression\Operator\LogicalOperator;
use Conjoon\Statement\OperandList;
use Conjoon\Statement\Value;
use Conjoon\Statement\VariableName;
use Tests\TestCase;

/**
 * Tests FunctionalOperator.
 */
class LogicalExpressionTest extends TestCase
{
    /**
     * Tests class
     */
    public function testClass()
    {
        $uses = class_uses(LogicalExpression::class);
        $this->assertContains(OperatorCallTrait::class, $uses);

        $this->assertSame(LogicalOperator::class, LogicalExpression::getOperatorClass());

        $operator = LogicalOperator::AND;

        $operands = [
            $this->createMockForAbstract(Operand::class),
            $this->createMockForAbstract(Operand::class),
            $this->createMockForAbstract(Operand::class)
        ];

        $expression = new LogicalExpression(
            $operator,
            OperandList::make(...$operands)
        );

        $this->assertInstanceOf(Expression::class, $expression);

        foreach ($operands as $index => $ops) {
            $this->assertSame($ops, $expression->getOperands()[$index]);
        }

        $expression = LogicalExpression::make(
            $operator,
            ...$operands
        );

        $this->assertInstanceOf(LogicalExpression::class, $expression);
        foreach ($operands as $index => $ops) {
            $this->assertSame($ops, $expression->getOperands()[$index]);
        }
    }


    /**
     * Tests constructor with AND andInvalidOperandException
     */
    public function testConstructorWithAndOperatorInvalidOperandException()
    {
        $this->expectException(InvalidOperandException::class);
        $this->expectExceptionMessage("expects at least 2 operands");

        $operator = LogicalOperator::AND;
        new LogicalExpression(
            $operator,
            new OperandList()
        );
    }


    /**
     * Tests constructor with NOT and InvalidOperandException
     */
    public function testConstructorWithNegationOperatorInvalidOperandException()
    {
        $this->expectException(InvalidOperandException::class);
        $this->expectExceptionMessage("expects 1 operand");

        $operands = OperandList::make(
            $this->createMockForAbstract(Operand::class),
            $this->createMockForAbstract(Operand::class)
        );
        $operator = LogicalOperator::NOT;
        new LogicalExpression(
            $operator,
            $operands
        );
    }


    /**
     * Tests constructor with OR operator and InvalidOperandException
     */
    public function testConstructorWithOrOperatorInvalidOperandException()
    {
        $this->expectException(InvalidOperandException::class);
        $this->expectExceptionMessage("expects at least 2 operands");

        $operator = LogicalOperator::OR;
        new LogicalExpression(
            $operator,
            new OperandList()
        );
    }

    /**
     * Tests toString()
     */
    public function testToString()
    {
        $operator = LogicalOperator::AND;
        $expression = LogicalExpression::make(
            $operator,
            new Value(1),
            new Value(2)
        );

        $this->assertSame("(1&&2)", $expression->toString());

        $operator = LogicalOperator::OR;
        $lftOp = LogicalOperator::AND;
        $rtOp = LogicalOperator::NOT;

        $notExpression = LogicalExpression::make($rtOp, new VariableName("a"));

        $expression = LogicalExpression::make(
            $operator,
            LogicalExpression::make($lftOp, new VariableName("x"), new VariableName("y")),
            $notExpression
        );

        $this->assertSame(
            "((x&&y)||(!a))",
            $expression->toString()
        );
    }
}
