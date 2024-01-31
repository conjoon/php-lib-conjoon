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

use \Stringable;
use Conjoon\Core\Contract\Arrayable;

/**
 * An interface allowing for exposing details about the object that caused the error.
 */
interface ErrorSource extends Stringable, Arrayable
{
    /**
     * Returns a string for identifying the ErrorSource.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the source this ErrorSource represents. If the ErrorSource is implemented by the Object
     * that caused the error, this method should return itself.
     *
     * @return string
     */
    public function getSource(): object;
}
