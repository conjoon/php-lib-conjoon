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

namespace Conjoon\JsonApi;

use Conjoon\Http\Request;

/**
 * A RequestMatcher resolves a Conjoon\Http\Request to a Request.
 */
abstract class RequestMatcher
{
    /**
     * Tries to match the specified $request and returns a Request if matching was successful.
     *
     * @param Request $request
     * @return Request|null
     */
    abstract public function match(Request $request): ?Request;
}
