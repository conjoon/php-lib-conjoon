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

namespace Tests\Conjoon\Core\Util;

use Conjoon\Core\Util\ArrayUtil;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Class ArrayUtilTest
 * @package Tests\Conjoon\Util
 */
class ArrayUtilTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests only()
     */
    public function testIntersect()
    {

        $data = [
            1, 2, 3, 4
        ];

        $keys = [1, 3];

        $this->assertEquals([1 => 2, 3 => 4], ArrayUtil::only($data, $keys));

        $data = [
            "foo" => "bar", "bar" => "snafu", 3 => 4
        ];

        $keys = ["foo", "bar"];

        $this->assertEquals(["foo" => "bar", "bar" => "snafu"], ArrayUtil::only($data, $keys));


        $data = [
            "foo" => "bar", "bar" => "snafu", 3 => 4
        ];

        $keys = ["foo", "bar"];

        $this->assertEquals(["foo" => "bar", "bar" => "snafu"], ArrayUtil::only($data, $keys));
    }


    /**
     * Tests mergeIf()
     */
    public function testMergeIfExceptionTarget()
    {
        $this->expectException(InvalidArgumentException::class);
        $target = ["foo" => "bar", 1 => "snafu", "3i" => 4];
        $data   = ["foo" => "snafu", "foobar" => "foo", "3i" => "6"];
        ArrayUtil::mergeIf($target, $data);
    }


    /**
     * Tests mergeIf()
     */
    public function testMergeIfExceptionData()
    {
        $this->expectException(InvalidArgumentException::class);
        $target = ["foo" => "bar", "ds" => "snafu", "3i" => 4];
        $data   = ["foo" => "snafu", "foobar" => "foo", "3" => "6"];
        ArrayUtil::mergeIf($target, $data);
    }


    /**
     * Tests mergeIf()
     */
    public function testMergeIf()
    {

        $target = [
            "foo" => "bar", "bar" => "snafu", "3i" => 4
        ];

        $data = [
            "foo" => "snafu", "foobar" => "foo", "3i" => "6"
        ];

        $this->assertEquals(
            ["foo" => "bar", "bar" => "snafu", "3i" => 4, "foobar" => "foo"],
            ArrayUtil::mergeIf($target, $data)
        );
    }


    /**
     * Tests unchain()
     */
    public function testUnchain()
    {

        $target = [
            "foo" => ["bar" => 34]
        ];

        $this->assertSame(34, ArrayUtil::unchain("foo.bar", $target));

        $target = [
            "foo" => ["bar" => ["snafu" => 2]]
        ];

        $this->assertSame(2, ArrayUtil::unchain("foo.bar.snafu", $target));

        $this->assertNull(ArrayUtil::unchain("foo.no.snafu", $target));
    }


    /**
     * Tests hasOnlyKeys
     */
    public function testHasOnly()
    {
        $data = [
            "foo" => "bar", "snafu" => "foo"
        ];

        $this->assertFalse(ArrayUtil::hasOnly([], ["foo", "foo", "snafu", "foo", "snafu"]));
        $this->assertTrue(ArrayUtil::hasOnly($data, ["foo", "foo", "snafu", "foo", "snafu"]));
        $this->assertTrue(ArrayUtil::hasOnly($data, ["foo", "foo", "snafu", "foo", "snafu", "bar"]));
        $this->assertTrue(ArrayUtil::hasOnly($data, ["foo", "snafu"]));
        $this->assertFalse(ArrayUtil::hasOnly($data, ["foo"]));
        $this->assertTrue(ArrayUtil::hasOnly($data, ["foo", "snafu", "bar"]));
        $this->assertFalse(ArrayUtil::hasOnly($data, ["foo", "bar"]));
    }
}
