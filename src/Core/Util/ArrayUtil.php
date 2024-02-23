<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\Core\Util;

use InvalidArgumentException;

/**
 * Class ArrayUtil.
 */
class ArrayUtil
{
    /**
     * Returns true if the specified $target array consist only of the specified keys,
     * or of a subset of keys specified in $keys.
     *
     * @example
     *     $target = [
     *         "foo" => "bar", "snafu" => "foo"
     *     ];
     *     ArrayUtil::hasOnlyKeys($data, ["foo"]); // false
     *     ArrayUtil::hasOnlyKeys($data, ["foo", "snafu"]); // true
     *     ArrayUtil::hasOnlyKeys($data, ["foo", "snafu", "bar"]); // true
     *
     * @param array $target
     * @param array $keys
     * @return bool
     */
    public static function hasOnly(array $target, array $keys): bool
    {
        $keys = array_unique($keys);
        $targetKeys = array_keys($target);

        if (count($keys) < count($targetKeys) || count($targetKeys) == 0) {
            return false;
        }

        $res = array_filter($targetKeys, fn ($key) => array_search($key, $keys) === false);

        return count($res) === 0;
    }


    /**
     * Returns an array that contains only the keys specified in $keys
     *
     * @example
     *   $data = [
     *     "foo" => "bar", "bar" => "snafu", 3 => 4
     *  ];
     *  $keys = ["foo", "bar"];
     *
     *  ArrayUtil::only($data, $keys)); // returns ["foo" => "bar", "bar" => "snafu"]
     *
     * @param array $data
     * @param array $keys
     *
     * @return array
     */
    public static function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }


    /**
     * Returns a merged assoc array.
     * Keys that exist in $source and $target will not get overwritten.
     *
     * @param array $target
     * @param array $source
     * @return array
     * @example
     *   $target = [
     *     "foo" => "bar", "bar" => "snafu", "0_3" => 4
     *  ];
     *  $source = ["foo" => "nono!", "anotherBar" => "yap"];
     *
     *  ArrayUtil::assign($target, $source));
     *             // returns ["foo" => "bar",
     *             //          "bar" => "snafu",
     *             //          "anotherBar" => "yap",
     *             //          "0_3" => 4
     *             //         ]
     *
     * @throws InvalidArgumentException if $target or $source contain numeric keys
     */
    public static function mergeIf(array $target, array $source): array
    {
        $chk = function ($value, $key) {
            if (is_int($key)) {
                throw new InvalidArgumentException("argument must not contain numeric keys");
            }
        };

        array_walk($source, $chk);
        array_walk($target, $chk);

        $new = array_merge([], $target);
        foreach ($source as $key => $item) {
            if (!array_key_exists($key, $new)) {
                $new[$key] = $item;
            }
        }
        return $new;
    }


    /**
     * String replacement for optional chaining operator.
     *
     * @param string $chain
     * @param array $target
     * @param null $default
     *
     * @example
     *
     *    $source = [
     *        "foo" => ["bar" => 34]
     *    ];
     *    ArrayUtil::unchain("foo.bar", $source); // 34
     *
     *    $source = [
     *        "foo" => ["snafu" => ["bar" => 34]]
     *    ];
     *    ArrayUtil::unchain("foo.bar", $source); // null
     *
     *    $source = [
     *        "foo" => ["snafu" => ["bar" => 34]]
     *    ];
     *    ArrayUtil::unchain("foo.bar", $source, true); // true
     *
     *
     *
     * @return mixed|null
     */
    public static function unchain(string $chain, array $target, $default = null)
    {
        if (!is_array($target)) {
            return $default;
        }

        $parts = explode(".", $chain);

        while (is_array($target) && count($parts)) {
            $target = $target[array_shift($parts)] ?? null;
        }

        if (!$target) {
            return $default;
        }

        return $target;
    }
}
