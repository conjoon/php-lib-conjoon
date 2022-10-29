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

namespace Conjoon\Net;

use BadMethodCallException;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Net\Exception\UriSyntaxException;

/**
 * Represents a Uniform Resource Identifier (URI).
 *
 * @method getScheme()
 * @method getUser()
 * @method getPass()
 * @method getHost()
 * @method getPort()
 * @method getPath()
 * @method getQuery()
 * @method getFragment()
 *
 * @phpstan-consistent-constructor
 */
class Uri implements Stringable
{
    /**
     * @var string
     */
    private readonly string $uri;

    /**
     * After successfully parsing the $uri into it's parts, an array with the name of the parts
     * mapped to their value is available.
     *
     * @var array<string, string|int>
     */
    private readonly array $parts;


    public static function create(string $uri): static
    {
        return new static($uri);
    }

    /**
     * @param string $uri
     *
     * @throws UriSyntaxException
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new UriSyntaxException(
                "Could not parse \"$uri\" into its parts"
            );
        }

        $this->parts = $parts;
    }


    /**
     * An URI is considered to be absolute if it has a scheme specified.
     *
     * @return bool true if this URI is absolute, otherwise false.
     */
    public function isAbsolute(): bool
    {
        return $this->getScheme() !== null;
    }

    /**
     * Delegates to various getters available for the components of a URI.
     * Getters are available for
     * "scheme", "user", "pass", "host", "port", "path", "query", "fragment".
     *
     * @param string $name
     * @param array<int, mixed> $arguments
     *
     * @return mixed|string|null
     *
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        $parts = ["scheme", "user", "pass", "host", "port", "path", "query", "fragment"];

        $nameToPart = strtolower(substr($name, 3));

        if (in_array($nameToPart, $parts)) {
            return $this->parts[$nameToPart] ?? null;
        }

        throw new BadMethodCallException(
            "No method named \"$name\" found in " . static::class
        );
    }


    /**
     * @param StringStrategy|null $stringStrategy
     * @return string
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        return $stringStrategy ? $stringStrategy->toString($this) : $this->uri;
    }
}
