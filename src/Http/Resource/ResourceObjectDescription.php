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

namespace Conjoon\Http\Resource;

/**
 * Description for a Resource Object that can be used by servers and clients
 * to discover structure, optional and default fields of resource object that
 * are request from the server.
 *
 */
abstract class ResourceObjectDescription
{
    /**
     * Returns the type of this entity used as an identifier with clients and
     * requests, e.g. its class-name.
     *
     * @return string
     */
    abstract public function getType(): string;


    /**
     * Returns all relationships of the resource object described by this class.
     *
     * @return ResourceObjectDescriptionList
     */
    abstract public function getRelationships(): ResourceObjectDescriptionList;


    /**
     * Returns all discoverable fields for this entity.
     *
     * @return string[]
     */
    abstract public function getFields(): array;


    /**
     * Returns the default configuration for fields. If fields are mapped with
     * options in a query, default options can be provided with this method.
     * The following provides the structure for a resource object representing
     * a message, where the "subject" field is considered as included by default
     * (if not requested otherwise), and where the "text" field should be trimmed
     * to a length of 200 characters, if not requested otherwise.
     *
     * @example
     *    [
     *       "subject" => true,
     *       "text" => ["length" : 200]
     *    ]
     *
     * @return array
     *
     * @see ResourceQuery
     */
    abstract public function getDefaultFields(): array;
}
