<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Core;

use ArrayAccess;
use Conjoon\Core\Contract\Arrayable;
use Countable;
use Iterator;
use OutOfBoundsException;
use TypeError;

/**
 * Class AbstractList.
 * Type-safe approach for maintaining lists holding element of a specific data type.
 *
 * @template TValue
 * @implements Iterator<int, TValue>
 * @implements  ArrayAccess<int, TValue>
 */
abstract class AbstractList implements Arrayable, ArrayAccess, Iterator, Countable
{
    /**
     * @var array<int, TValue>
     */
    protected array $data = [];

    /**
     * \Iterator Interface
     * @var int
     */
    protected int $position = 0;


    /**
     * Constructor.
     * Final to allow new static();
     *
     * @see make
     */
    final public function __construct()
    {
    }


    /**
     * Factory method for easily creating instances of the implementing class.
     *
     * @param mixed ...$items
     *
     * @return static
     */
    public static function make(...$items): static
    {
        $self = new static();

        foreach ($items as $item) {
            $self[] = $item;
        }

        return $self;
    }


    /**
     * Returns the class name of the entity-type this list should maintain
     * entries of.
     *
     * @return string
     */
    abstract public function getEntityType(): string;

    /**
     * Applies the map function to this data and returns it.
     *
     * @param callable $mapFn The callable to pass to the callback submitted to
     * array_map()
     *
     * @return array<mixed, mixed>
     */
    public function map(callable $mapFn): array
    {
        return array_map($mapFn, $this->data);
    }


    /**
     * Returns the entry in this list given the callback function.
     *
     * @param callable $findFn A callback. Return true in the function to indicate a match. First match will
     * be returned. The callback is passed the current entry.
     *
     * @return ?TValue
     */
    public function findBy(callable $findFn): mixed
    {
        foreach ($this->data as $resource) {
            if ($findFn($resource) === true) {
                return $resource;
            }
        }

        return null;
    }


    /**
     * Returns the element at the head of the AbstractList, or null if the list is empty.
     *
     * @return ?TValue
     */
    public function peek(): mixed
    {
        $count = count($this->data);
        return !$count ? null : $this->data[$count - 1];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     *
     * @throws OutOfBoundsException
     */
    protected function doInsert(mixed $offset, mixed $value)
    {
        if (!is_null($offset) && !is_int($offset)) {
            throw new OutOfBoundsException(
                "expected integer key for \"offset\", " .
                "but got type: " . (gettype($offset))
            );
        }

        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param mixed  $value
     * @return bool
     *
     * @throws TypeError
     */
    protected function assertTypeFor(mixed $value): bool
    {
        $entityType = $this->getEntityType();

        // instanceof has higher precedence, do
        // (!$value instanceof $entityType)
        // would also be a valid expression
        if (!($value instanceof $entityType)) {
            /** @var object $value */
            throw new TypeError(
                "Expected type \"$entityType\" for value-argument, got " . get_class($value)
            );
        }

        return true;
    }


// -------------------------
//  ArrayAccess Interface
// -------------------------

    /**
     * @inheritdoc
     *
     * @throws TypeError|OutOfBoundsException if $value is not of the type defined
     * with this getEntityType, or f $offset is not an int
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertTypeFor($value);
        $this->doInsert($offset, $value);
    }


    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }


    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }


    /**
     * @inheritdoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }


// --------------------------
//  Iterator Interface
// --------------------------

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

// --------------------------
//  Iterator Interface
// --------------------------

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }


// --------------------------
//  Arrayable interface
// --------------------------

    /**
     * @return array<mixed, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
