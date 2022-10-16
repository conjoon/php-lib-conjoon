<?php

/**
 * conjoon
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

namespace Conjoon\Data\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Error\Errors;

/**
 * Contract for implementing validation rules.
 */
interface Rule
{
    /**
     * Method for testing if the specified object satisfies the rules defined with
     * this method. Returns true if the supplied object is assumed to be valid,
     * otherwise false.
     * If validating $obj fails, implementing APIs are advised to add a ValidationError object to $errors, containing
     * more detailed information about the violations encountered.
     *
     * @param Object $obj The object to test for validity.
     * @param ValidationErrors $errors A list of errors for storing the current result of validating $obj.
     * May contain information about previously failed validation attempts of this $obj or any other object in
     * a queue of validations
     *
     * @return bool true if the $obj ior a part of it is considered to be valid, otherwise false.
     *
     * @throws UnexpectedTypeException
     */
    public function isValid(object $obj, ValidationErrors $errors): bool;
}
