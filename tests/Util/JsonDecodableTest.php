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

namespace Tests\Conjoon\Util;

use Conjoon\Util\Jsonable;
use Conjoon\Util\JsonDecodable;
use Conjoon\Util\JsonStrategy;
use Tests\TestCase;

/**
 * Class JsonDecodableTest
 * @package Tests\Conjoon\Util
 */
class JsonDecodableTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testInterface()
    {
        $c = new class implements JsonDecodable {
            public static function fromString(string $value): Jsonable
            {
                $t = new class implements Jsonable {
                    public function toJson(JsonStrategy $strategy = null): array
                    {
                        return[];
                    }
                };
                return new $t();
            }

            public static function fromArray(array $arr): Jsonable
            {
                $t = new class implements Jsonable {
                    public function toJson(JsonStrategy $strategy = null): array
                    {
                        return[];
                    }
                };
                return new $t();
            }
        };

        $this->assertInstanceOf(JsonDecodable::class, $c);
    }
}
