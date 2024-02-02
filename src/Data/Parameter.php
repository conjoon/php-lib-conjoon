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

namespace Conjoon\Data;

use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Error\ErrorSource;

class Parameter implements ErrorSource
{
    /**
     * @var string
     */
    private string $value;

    /**
     * @var string
     */
    private string $name;


    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }


    /**
     * Returns the value this Parameter was created with.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Textual representation of this parameter's name.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @inheritdoc
     */
    public function getSource(): object
    {
        return $this;
    }


    /**
     * @param StringStrategy|null $stringStrategy
     * @Override
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        return $this->getName() . "=" . $this->getValue();
    }

    /**
     * @inheritdoc
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ["parameter" => $this->getName()];
    }
}
