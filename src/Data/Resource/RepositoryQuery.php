<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Data\Resource;

use BadMethodCallException;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Data\ParameterBag;

/**
 * A RepositoryQuery provides an interface for a validated and certified collection
 * of parameters that can safely be passed to the low level API.
 * The origin of the parameters is arbitrary and not of interest to this class,
 * as they are validated and sanitized before they end up with a ParameterBag in an instance
 * of this class.
 * Any class working with a RepositoryQuery can rely on the validity, mutually exclusivity
 * and key-/value-pair-correctness of the wrapped ParameterBag this class guarantees.
 * Note:
 * This RepositoryQuery delegates all method calls involving getters to the ParameterBag
 * using __call, including querying the properties using __get.
 *
 * @method string|null getString(string $name)
 * @method int|null getInt(string $name)
 * @method bool|null getBool(string $name)
 */
abstract class RepositoryQuery implements Jsonable
{
    /**
     * @var ParameterBag
     */
    protected ParameterBag $parameterBag;

    /**
     * RepositoryQuery constructor.
     * @param ParameterBag $parameterBag
     */
    public function __construct(ParameterBag $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }


    /**
     * Delegates to the ParameterBag's __call.
     *
     * @param string $method
     * @param array<int, mixed> $arguments
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments)
    {
        return $this->parameterBag->{$method}(...$arguments);
    }


    /**
     * Delegates to the ParameterBag's __get.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->parameterBag->{$key};
    }


    /**
     * Delegates to the ParameterBag's has().
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->parameterBag->has($key);
    }


    /**
     * Delegates to the ParameterBag's toJson().
     *
     * @return array<mixed, mixed>
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        return $this->parameterBag->toJson($strategy);
    }


    /**
     * Returns the ResourceDescription this resource query targets.
     * @return ResourceDescription
     */
    abstract public function getResourceTarget(): ResourceDescription;
}
