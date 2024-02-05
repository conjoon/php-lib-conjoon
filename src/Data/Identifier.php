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

use \Stringable;
use Conjoon\Core\Contract\Arrayable;


/**
 * Abstract representation of a data-identifier.
 */
abstract class Identifier implements Arrayable, Stringable
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Returns an array representation of this object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId()
        ];
    }


    /**
     * @Override
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}
