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

use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Statement\Expression\Expression;
use Conjoon\Statement\Expression\Notation\PolishNotation;
use Conjoon\Statement\Expression\Operator\Operator;
use Conjoon\Statement\Expression\RelationalExpression;
use Conjoon\Statement\Expression\Operator\RelationalOperator;
use Conjoon\Statement\InvalidOperandException;
use Conjoon\Statement\Operand;
use Conjoon\Statement\OperandList;
use Conjoon\Statement\Value;
use Conjoon\Core\Data\StringStrategy;
use Conjoon\Statement\VariableName;
use Tests\TestCase;

/**
 * Tests PolishNotation.
 */
class PolishNotationTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $pn = new PolishNotation();
        $this->assertInstanceOf(StringStrategy::class, $pn);
    }


    /**
     * Tests toString() with UnexpectedTypeException
     */
    public function testToStringWithUnexpectedTypeException()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(Expression::class);

        $pn = new PolishNotation();

        $testClass = new class implements Stringable {
            public function toString(StringStrategy $stringStrategy = null): string
            {
                return $stringStrategy->toString($this);
            }
        };

        $testClass->toString($pn);
    }

    /**
     * Tests constructor with InvalidOperandException
     */
    public function testToString()
    {
        $operator = RelationalOperator::IS;
        $expression = RelationalExpression::make(
            $operator,
            new Value(1),
            new Value(2)
        );

        $this->assertSame("== 1 2", $expression->toString(new PolishNotation()));

        $operator = RelationalOperator::LESS_THAN_OR_EQUAL;
        $lftOp = RelationalOperator::IS_NOT;
        $rtOp = RelationalOperator::GREATER_THAN;

        $expression = RelationalExpression::make(
            $operator,
            RelationalExpression::make($lftOp, new VariableName("x"), new VariableName("y")),
            RelationalExpression::make($rtOp, new VariableName("a"), new VariableName("b"))
        );

        $this->assertSame(
            "((x!=y)<=(a>b))",
            $expression->toString()
        );

        $this->assertSame(
            "<= != x y > a b",
            $expression->toString(new PolishNotation())
        );
    }
}
