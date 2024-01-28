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

use Conjoon\Net\Uri;

abstract class PathMatcher
{

    public abstract function match(Uri $uri) : ?PathMatcherResult;

}