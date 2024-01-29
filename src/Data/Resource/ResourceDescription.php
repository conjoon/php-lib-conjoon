<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\Data\Resource;

use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Contract\StringStrategy;

/**
 * Description for Resource Objects used as conceptually representatives for
 * discoverable entities.
 *
 */
abstract class ResourceDescription implements Stringable
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
     * @return ResourceDescriptionList
     */
    abstract public function getRelationships(): ResourceDescriptionList;


    /**
     * Returns all discoverable fields for this Resource.
     *
     * @return string[]
     */
    abstract public function getFields(): array;


    /**
     * Returns the default fields this Resource exposes.
     *
     * @return array<int, string>
     *
     * @see RepositoryQuery
     */
    abstract public function getDefaultFields(): array;


    /**
     * Returns the getType() value of all the relationships available for this
     * resource description, along with all associated children.
     *
     * @param bool $withResourceTarget If true, returns the list including the resource
     * *this* ResourceDescription describes
     *
     * @return array<int, string>
     */
    public function getAllRelationshipTypes(
        bool $withResourceTarget = false
    ): array {
        $list = $this->getAllRelationshipResourceDescriptions($withResourceTarget);

        $res = $list->map(fn(ResourceDescription $rel) => $rel->getType());

        /**
         * @var array<int, string> $res
         */
        return $res;
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
     * @return array<int, string>
     */
    public function getAllRelationshipPaths(bool $withResourceTarget = false): array
    {
        $tree = [];
        $visited = [];
        $traverse = function ($resourceTarget, array $path = []) use (&$traverse, &$visited, &$tree) {

            $path[] = $resourceTarget->getType();

            $relationships = $resourceTarget->getRelationships();

            $tree[] = $path;
            foreach ($relationships as $child) {
                if (in_array($child, $visited)) {
                    continue;
                }
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
     * *this* ResourceDescription describes
     *
     *
     * @return ResourceDescriptionList
     */
    public function getAllRelationshipResourceDescriptions(
        bool $withResourceTarget = false
    ): ResourceDescriptionList {

        $list = new ResourceDescriptionList();


        if ($withResourceTarget === true) {
            $list[] = $this;
        }

        $traverse = function ($resourceObject) use (&$list, &$traverse) {

            $t = $resourceObject->getRelationships();

            foreach ($t as $rel) {
                if ($list->findBy(fn(ResourceDescription $item) => $item->getType() === $rel->getType())) {
                    continue;
                }
                $list[] = $rel;
                $traverse($rel);
            }
        };

        $traverse($this);

        return $list;
    }


    /**
     * @param StringStrategy|null $stringStrategy
     * @return string
     */
    public function toString(?StringStrategy $stringStrategy = null): string
    {
        return $stringStrategy ? $stringStrategy->toString($this) : $this->getType();
    }
}
