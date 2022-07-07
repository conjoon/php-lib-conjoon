<?php

/**
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

namespace Conjoon\JsonApi\Validation;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\JsonApi\Query;

/**
 * Class providing functionality for making sure a query contains only parameters
 * named after entries specified in a list. The list of allowed entries should be
 * returned by getAllowedParameterNames.
 */
class ValidParameterNamesRule extends Rule
{
    /**
     * @inheritdoc
     */
    protected function validate(Query $query, ValidationErrors $errors): bool
    {
        $allowed    = $this->getAllowedParameterNames($query);
        $parameters = $query->getAllParameterNames();
        $spill = array_diff($parameters, $allowed);
        if (count($spill) > 0) {
            $errors[] = new ValidationError(
                $query,
                "found additional parameters " .
                "\"" . implode("\", \"", $spill) . "\"",
                400
            );
            return false;
        }

        return true;
    }

    /**
     * Returns the list of allowed parameter names.
     *
     * @param Query $query The query containing the resource target
     *
     * @return array
     */
    public function getAllowedParameterNames(Query $query): array
    {
        $resourceTarget = $query->getResourceTarget();
        $exp = ["include", "fields[{$resourceTarget->getType()}]"];
        $list = $resourceTarget->getAllRelationshipPaths(false);
        foreach ($list as $type) {
            $exp[] = "fields[$type]";
        }

        return $exp;
    }
}
