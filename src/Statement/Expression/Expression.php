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

namespace Conjoon\Statement\Expression;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Statement\Expression\Operator\Operator;
use Conjoon\Statement\Operand;
use Conjoon\Core\Data\StringStrategy;
use Conjoon\Core\Data\JsonStrategy;
use Conjoon\Statement\OperandList;

/**
 * Represents an operation.
 */
abstract class Expression implements Stringable, Arrayable, Operand
{
    /**
     * @var OperandList
     */
    protected OperandList $operands;


    /**
     * @var Operator
     */
    protected Operator $operator;


    /**
     * Returns the operands used with this expression.
     *
     * @return OperandList
     */
    public function getOperands(): OperandList
    {
        return $this->operands;
    }


    /**
     * Returns the operator used with this expression.
     *
     * @return Operator
     */
    public function getOperator(): Operator
    {
        return $this->operator;
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        if (!$stringStrategy) {

            return "(" .
                    implode(
                        $this->getOperator()->toString(),
                        $this->getOperands()->toArray()
                    ) .
                ")";
        }

        return $stringStrategy->toString($this);
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $ops = $this->getOperands()->toArray();

        array_unshift(
            $ops,
            $this->getOperator()->toString()
        );

        return $ops;
    }


    /**
     * @inheritdoc
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        if (!$strategy) {
            return $this->toArray();
        }

        return $strategy->toJson($this);
    }
}
