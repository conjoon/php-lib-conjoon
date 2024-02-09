<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Math\Expression\Notation;

use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Data\ParameterBag;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\Notation\PolishNotation;
use Conjoon\Math\Expression\Notation\PolishNotationToExpression;
use Conjoon\Math\Expression\Notation\ToExpression;
use Conjoon\Math\Expression\Operator\Operator;
use Conjoon\Math\Expression\RelationalExpression;
use Conjoon\Math\Expression\Operator\RelationalOperator;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Operand;
use Conjoon\Math\OperandList;
use Conjoon\Math\Value;
use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Math\VariableName;
use Tests\TestCase;


class PolishNotationToExpressionTest extends TestCase
{

    public function testTransform() {


        $jsonFilter = [
            "OR" => [
                ["IN" => ["id" => ["o, [GMAIL], INBOX"]]],
                ["==" => ["id" => 5]],
                ["OR" => [
                    ["IN" => ["foo" => [1, 2, 2]]],
                    ["IN" => ["BAR" => [1, 2, 3]]]
                ]]
            ]
        ];
        $bag = new ParameterBag(["filter" => json_encode($jsonFilter)]);

        $transformer = new PolishNotationToExpression();
        $expression = $transformer->transform($jsonFilter);

        $this->assertSame("|| IN id (o, [GMAIL], INBOX) == id 5 || IN foo (1, 2, 2) IN BAR (1, 2, 3)", $expression->toString(new PolishNotation()));




    }
}
