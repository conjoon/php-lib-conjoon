<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\Net;

use BadMethodCallException;
use Conjoon\Core\Contract\Equatable;
use \Stringable;
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
class Uri implements Stringable, Equatable
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


    public static function make(string $uri): static
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


    public function equals(object $target): bool
    {
        if (!($target instanceof Uri)) {
            return false;
        }
        return strtolower((string) $this) === strtolower((string) $target);
    }


    /**
     * @Override
     */
    public function __toString()
    {
        return $this->uri;
    }
}
