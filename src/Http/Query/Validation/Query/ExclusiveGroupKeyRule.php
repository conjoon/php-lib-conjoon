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

namespace Conjoon\Http\Query\Validation\Query;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\ParameterTrait;
use Conjoon\Http\Query\Query;

/**
 * Rule for validating that a group key is exclusive with parameter groups.
 * Validates that any keyis not appearing more than one time as the group key
 * for a specific group parameter.
 *
 * @example
 *    // query: ?fields[MessageItem]=subject&relfield:fields[MessageItem]=+previewText
 *    $rule = new ExclusiveGroupNameRule(["fields", "relfield:fields"]);
 *
 */
class ExclusiveGroupKeyRule extends QueryRule
{
    use ParameterTrait;

    /**
     * @var array
     */
    protected array $groups;

    /**
     * @var array
     */
    protected array $found;

    /**
     * Constructor.
     *
     * @param array $groups The name of the parameter groups for which a key may not
     * appear twice.
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }


    /**
     * @inheritdoc
     */
    protected function validate(Query $query, ValidationErrors $errors): bool
    {
        $parameters = $query->getAllParameters();

        // reset/ initialize found for each run
        $this->found = [];

        foreach ($parameters as $parameter) {
            // skip, if not a group parameter
            if (!$this->isGroupParameter($parameter)) {
                continue;
            }

            $name = $this->getGroupName($parameter);

            // skip, if the group parameter is not configured with *this* groups
            if (!in_array($name, $this->groups)) {
                continue;
            }

            $key = $this->getGroupKey($parameter);

            if (!array_key_exists($key, $this->found)) {
                $this->found[$key] = $name;
            } else {
                $errors[] = new ValidationError(
                    $query,
                    "\"$key\" must not appear in group \"$name\", since it was already " .
                    "configured with \"" . $this->found[$key] . "\"",
                    400
                );

                return false;
            }
        }

        return true;
    }
}
