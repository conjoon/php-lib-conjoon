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

namespace Conjoon\Net;

use Conjoon\Net\Exception\MalformedUrlException;

/**
 * Represents a Uniform Resource Locator (URL).
 */
class Url extends Uri
{
    /**
     * @param string $uri An absolute uri this instance should represent.
     *
     * @throws MalformedUrlException
     */
    public function __construct(string $uri)
    {
        parent::__construct($uri);

        if (!$this->isAbsolute()) {
            throw new MalformedUrlException(
                "An URL must be an absolute URI, got \"$uri\""
            );
        }
    }
}
