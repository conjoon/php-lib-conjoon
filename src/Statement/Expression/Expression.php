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
use Conjoon\Core\Data\Operand;
use Conjoon\Statement\Expression\Operator\Operator;
use Conjoon\Core\Data\StringStrategy;

/**
 * Represents an operation.
 */
abstract class Expression implements Stringable, Arrayable
{
    /**
     * Returns the operand used with this operation.
     * @return Operand
     */
    abstract public function getOperand(): Operand;


    /**
     * Returns the operator used with this operation,
     * @return Operator
     */
    abstract public function getOperator(): Operator;


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        if (!$stringStrategy) {
            return implode("", [$this->getOperator()->toString(), $this->getOperand()->toString()]);
        }

        return $stringStrategy->toString($this);
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [$this->getOperator()->toString(), $this->getOperand()->toString()];
    }
}
