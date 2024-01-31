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

namespace Conjoon\Error;

use Conjoon\Core\Contract\StringStrategy;

/**
 * Class representing objects that do not implement the ErrorSource interface.
 */
class AnonymousErrorSource implements ErrorSource
{
    /**
     * @var Object
     */
    protected object $source;

    /**
     * @param Object $source
     */
    public function __construct(object $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return
            "anonymous<" .
            str_replace("\n", "", print_r($this->source, true)) .
            ">";
    }

    /**
     * @Override
     */
    public function __toString(): string
    {
        return $this->getName();
    }


    /**
     * Returns the source object this class encapsulates.
     * @return object
     */
    public function getSource(): object
    {
        return $this->source;
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return ["pointer" => $this->getName()];
    }
}
