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

namespace Conjoon\Math;

use Conjoon\Core\Strategy\JsonStrategy;
use Conjoon\Core\Strategy\StringStrategy;

/**
 * Represents an arbitrary Value.
 */
class Value implements Operand
{
    /**
     * @var mixed
     */
    protected mixed $value;


    /**
     * Creates a new Value.
     *
     * @param mixed $value
     *
     * @return Value
     */
    public static function make(mixed $value): Value
    {
        return new self($value);
    }


    /**
     * Constructor.
     *
     * @param mixed $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            $this->getValue()
        ];
    }


    /**
     * @param JsonStrategy|null $strategy
     * @return array
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        if (!$strategy) {
            return $this->toArray();
        }

        return $strategy->toJson($this);
    }


    /**
     * @param StringStrategy|null $stringStrategy
     * @return string
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        if (!$stringStrategy) {
            $value = $this->getValue();

            switch (true) {
                case ($value === true):
                    return "true";
                case ($value === false):
                    return "false";
                default:
                    return (string)$value;
            }
        }

        return $stringStrategy->toString($this);
    }
}
