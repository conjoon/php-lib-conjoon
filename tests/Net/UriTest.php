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

use BadMethodCallException;
use Conjoon\Net\Exception\UriSyntaxException;
use Conjoon\Net\Uri;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * tests Uri
 */
class UriTest extends TestCase
{
    use StringableTestTrait;

    public function testConstructorWithUriSyntaxException(): void
    {
        $this->expectException(UriSyntaxException::class);

        $this->createInstanceToTest(["https://host:port"]);
    }


    public function testMagicCallToGetters(): void
    {
        $parts = ["scheme", "user", "pass", "host", "port", "path", "query", "fragment"];

        $tests = $this->getTestsForMagicCallToGetters();

        foreach ($tests as $test) {
            ["input" => $input, "output" => $output, "isAbsolute" => $isAbsolute] = $test;

            $uri = $this->createInstanceToTest([$input]);

            foreach ($parts as $part) {
                $method = "get" . ucfirst($part);
                $this->assertSame($output[$part] ?? null, $uri->{$method}());
            }

            $this->assertSame($isAbsolute, $uri->isAbsolute());
        }
    }


    public function testMagicCallWithBadMethodCallException(): void
    {
        $this->expectException(BadMethodCallException::class);

        $uri = $this->createInstanceToTest(["https://uri.com"]);

        $uri->methodDoesNotExist();
    }


    public function testToString(): void
    {
        $this->runToStringTest($this->getClassToTest());
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
            "input" => "./path?query=string",
            "output" => [
                "path" => "./path",
                "query" => "query=string"
            ],
            "isAbsolute" => false
        ], [
            "input" => "https://localhost/../path",
            "output" => [
                "scheme" => "https",
                "host" => "localhost",
                "path" => "/../path"
            ],
            "isAbsolute" => true
        ], [
            "input" => "//localhost/../path",
            "output" => [
                "host" => "localhost",
                "path" => "/../path"
            ],
            "isAbsolute" => false
        ]];
    }


    protected function createInstanceToTest(array $arguments): Uri
    {
        $className = $this->getClassToTest();
        return new $className(...$arguments);
    }


    protected function getClassToTest()
    {
        return Uri::class;
    }
}
