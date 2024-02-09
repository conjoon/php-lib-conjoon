<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Core\Data\Filter\Operation;

use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\OperatorCallTrait;
use Conjoon\Math\Expression\FunctionalExpression;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Operand;
use Conjoon\Math\OperandList;
use Conjoon\Math\Value;
use Conjoon\Math\VariableName;
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
