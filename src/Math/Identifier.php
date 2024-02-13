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

namespace Conjoon\Math;

use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Core\Contract\StringStrategy;

/**
 * Represents the name of a variable, i.e. a symbolic name for some storage location.
 */
class Identifier implements Operand
{
    /**
     * @var string
     */
    protected string $name;


    /**
     * Creates a new Identifier.
     *
     * @param string $name
     *
     * @return Identifier
     */
    public static function make(string $name): Identifier
    {
        return new self($name);
    }


    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     * @deprecated use name()
     */
    public function getValue(): string
    {
        return $this->name();
    }

    public function name(): string {
        return $this->name;
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
            return $this->getValue();
        }

        return $stringStrategy->toString($this);
    }

    public function __toString() {
        return $this->toString();
    }
}
