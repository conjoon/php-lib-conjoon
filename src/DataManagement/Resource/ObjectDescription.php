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

namespace Conjoon\DataManagement\Resource;

/**
 * Description for a Resource Object that can be used by servers and clients
 * to discover structure, optional and default fields of resource object that
 * are request from the server.
 *
 */
abstract class ObjectDescription
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
     * @return ObjectDescriptionList
     */
    abstract public function getRelationships(): ObjectDescriptionList;


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


    /**
     * Returns the getType() value of all of the relationships available for this
     * resource description, along with all children of the resource object represented by
     * an relationship.
     *
     * @param bool $withResourceTarget If true, returns the list including the resource
     * *this* ObjectDescription describes
     *
     * @return array
     */
    public function getAllRelationshipTypes(
        bool $withResourceTarget = false
    ): array {
        $list = $this->getAllRelationshipResourceDescriptions($withResourceTarget);

        return $list->map(fn($rel) => $rel->getType());
    }


    /**
     * Returns the getType() value of all the relationships available for this
     * resource and its child relationships, in dot-notation.
     * If this resource is A, and it has the relationships to B, which has a
     * relationship to C and D, the following values are returned for
     * $withResourceTarget = true
     * <pre>
     * A
     * AB
     * ABC
     * ABD
     * </pre>
     * if $withResourceTarget is set to false, the following values are returned:
     * <pre>
     * B
     * BC
     * BD
     * </pre>
     *
     * Note: The method tries to avoid circular dependencies and will not visit a resource that
     * has previously been visited.
     *
     * @param bool $withResourceTarget If true, returns the list including *this*
     *
     * @return array
     */
    public function getAllRelationshipPaths(bool $withResourceTarget = false): array
    {
        $tree = [];
        $visited = [];
        $traverse = function ($resourceTarget, array $path = []) use (&$traverse, $withResourceTarget, &$visited, &$tree) {

            $path[] = $resourceTarget->getType();

            $relationships = $resourceTarget->getRelationships();

            $tree[] = $path;
            foreach ($relationships as $child) {
                if (in_array($child, $visited)) {
                    continue;
                };
                $visited[] = $child;
                $traverse($child, $path);
            }
        };

        if ($withResourceTarget) {
            $visited = [$this];
            $traverse($this);
        } else {
            foreach ($this->getRelationships() as $node) {
                $traverse($node);
            }
        }

        return array_map(fn ($path) => implode(".", $path), $tree);
    }


    /**
     * Returns all resource object description available with all relationships spawning
     * from the resource object target for this instance and its related resources.
     * Note: The method tries to avoid circular dependencies and will not visit a resource that
     * has previously been visited.
     *
     * @param bool $withResourceTarget If true, returns the list including the resource
     * *this* ObjectDescription describes
     *
     *
     * @return ObjectDescriptionList
     */
    public function getAllRelationshipResourceDescriptions(
        bool $withResourceTarget = false
    ): ObjectDescriptionList {

        $list = new ObjectDescriptionList();


        if ($withResourceTarget === true) {
            $list[] = $this;
        }

        $traverse = function ($resourceObject) use (&$list, &$traverse) {

            $t = $resourceObject->getRelationships();

            foreach ($t as $rel) {
                if ($list->findBy(fn(ObjectDescription $item) => $item->getType() === $rel->getType())) {
                    continue;
                }
                $list[] = $rel;
                $traverse($rel);
            }
        };

        $traverse($this);

        return $list;
    }
}
