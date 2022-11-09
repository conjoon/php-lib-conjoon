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

namespace Conjoon\Web\Validation\Query\Rule;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query;
use Conjoon\Web\Validation\Query\QueryRule;

/**
 * Class providing functionality for making sure a query contains parameters
 * named after entries specified in a list.
 */
class RequiredParameterNamesRule extends QueryRule
{
    /**
     * @var array<int, string>
     */
    private array $required;


    /**
     * Constructor.
     *
     * @param array<int, string> $required A list of required parameter names.
     */
    public function __construct(array $required)
    {
        $this->required = $required;
    }


    /**
     * @inheritdoc
     */
    protected function validate(Query $query, ValidationErrors $errors): bool
    {
        $allowed = $this->getRequired();
        $parameters = $query->getAllParameterNames();
        $spill = array_diff($allowed, $parameters);
        if (count($spill) > 0) {
            $errors[] = new ValidationError(
                $query,
                "required parameters missing " .
                "\"" . implode("\", \"", $spill) . "\"",
                400
            );
            return false;
        }

        return true;
    }


    /**
     * Returns the list of required parameter names.
     *
     * @return array<int, string>
     */
    public function getRequired(): array
    {
        return $this->required;
    }
}
