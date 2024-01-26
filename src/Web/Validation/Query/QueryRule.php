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

namespace Conjoon\Web\Validation\Query;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Data\Validation\Rule as ValidationRule;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query;

/**
 * Rule specific for Http Queries.
 */
abstract class QueryRule implements ValidationRule
{
    /**
     * True if this rule supports the passed object for validation
     * @param Object $obj
     *
     * @return bool
     */
    public function supports(object $obj): bool
    {
        return $obj instanceof Query;
    }


    /**
     * @inheritdoc
     *
     * @see validate()
     */
    final public function isValid(object $obj, ValidationErrors $errors): bool
    {
        if (!$this->supports($obj)) {
            throw new UnexpectedTypeException();
        }

        /**
         * @var Query $obj
         */
        return $this->validate($obj, $errors);
    }

    /**
     * Contract for validating the passed Query or parts of it.
     * Delegated from isValid.
     *
     * @param Query $query
     * @param ValidationErrors $errors
     *
     * @return bool true if validation succeeded, otherwise false
     */
    abstract protected function validate(Query $query, ValidationErrors $errors): bool;
}