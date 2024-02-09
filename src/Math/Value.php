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

namespace Conjoon\Math;

use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Core\Contract\StringStrategy;

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
     * @deprecated use value()
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value();
    }

    public function value() {
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
            $value = $this->value();

            switch (true) {
                case ($value === true):
                    return "true";
                case ($value === false):
                    return "false";
                case (is_array($value)):
                     return implode(", ", $value);
                default:
                    return (string)$value;
            }
        }

        return $stringStrategy->toString($this);
    }
}
