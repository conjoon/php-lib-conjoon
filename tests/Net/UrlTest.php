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

namespace Tests\Conjoon\Net;

use Conjoon\Net\Exception\MalformedUrlException;
use Conjoon\Net\Url;

/**
 * tests Url
 */
class UrlTest extends UriTest
{
    public function testConstructorWithMalformedUrlException(): void
    {
        $this->expectException(MalformedUrlException::class);

        $this->createInstanceToTest(["host/somePath"]);
    }


    protected function getTestsForMagicCallToGetters()
    {
        return [[
            "input" => "scheme://user:pass@host:8080/path?query#fragment",
            "output" => [
                "scheme" => "scheme",
                "user" => "user",
                "pass" => "pass",
                "host" => "host",
                "port" => 8080,
                "path" => "/path",
                "query" => "query",
                "fragment" => "fragment"
            ],
            "isAbsolute" => true
        ], [
            "input" => "https://localhost/../path",
            "output" => [
                "scheme" => "https",
                "host" => "localhost",
                "path" => "/../path"
            ],
            "isAbsolute" => true
        ]];
    }

    protected function createInstanceToTest(array $arguments): Url
    {
        $className = $this->getClassToTest();
        return new $className(...$arguments);
    }


    protected function getClassToTest()
    {
        return Url::class;
    }
}
