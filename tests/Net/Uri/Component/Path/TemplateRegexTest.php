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

namespace Tests\Conjoon\Net\Uri\Component\Path;

use Conjoon\Net\Uri\Component\Path\TemplateRegex;
use ReflectionException;
use Tests\TestCase;

/**
 * tests UriTemplateRegex
 */
class TemplateRegexTest extends TestCase
{
    /**
     * Tests getRegexString()
     * @return void
     * @throws ReflectionException
     */
    public function testGetRegexString(): void
    {
        $tests = [[
            "input" => "tpl",
            "output" => "/tpl\??[^\/]*$/m"
        ], [
            "input" => "/tpl/{resourceId}",
            "output" => "/^\/tpl\/(?<resourceId>[^\?\/]+)\??[^\/]*$/m"
        ], [
            "input" => "tpl/resource/{resourceId}/hierarchy/{subItemId}",
            "output" => "/tpl\/resource\/(?<resourceId>[^\?\/]+)\/" .
                "hierarchy\/(?<subItemId>[^\?\/]+)\??[^\/]*$/m"
        ], [
            "input" => "tpl/resource/{resourceId}/hierarchy/{subItemId}/index.php?query=foo",
            "output" => "/tpl\/resource\/(?<resourceId>[^\?\/]+)\/hierarchy" .
                        "\/(?<subItemId>[^\?\/]+)\/index.php\?query=foo\??[^\/]*$/m"
        ], [
            "input" => "tpl/{resourceId}",
            "output" => "/tpl\/(?<resourceId>[^\?\/]+)\??[^\/]*$/m"
        ]];

        foreach ($tests as $test) {
            ["input" => $input, "output" => $output] = $test;

            $pathTemplateRegex = new TemplateRegex($input);
            $getRegexString = $this->makeAccessible($pathTemplateRegex, "getRegexString");

            $this->assertSame($output, $getRegexString->invokeArgs($pathTemplateRegex, []));
        }
    }


    /**
     * Tests match()
     * @return void
     */
    public function testMatch(): void
    {
        $tests = [[
            "cArg" => "tpl",
            "input" => "tpl",
            "output" => []
        ], [
            "cArg" => "/tpl/{resourceId}",
            "input" => "someUri",
            "output" => null
        ], [
            "cArg" => "/Resources/{resourceId}",
            "input" => "/Resources",
            "output" => null
        ], [
            "cArg" => "/tpl/{resourceId}",
            "input" => "/tpl/1?someParam=someValue",
            "output" => [
                "resourceId" => "1"
            ]
        ], [
            "cArg" => "tpl/resource/{resourceId}/hierarchy/{subItemId}/subpart",
            "input" => "tpl/resource/123/hierarchy/456/subpart",
            "output" => [
                "resourceId" => "123",
                "subItemId" => "456"
            ]
        ], [
            "cArg" => "tpl/resource/{resourceId}/hierarchy/{subItemId}",
            "input" => "tpl/resource/123/hierarchy/456",
            "output" => [
                "resourceId" => "123",
                "subItemId" => "456"
            ]
        ], [
            "cArg" => "tpl/resource/{resourceId}/hierarchy/{subItemId}/index.php",
            "input" => "tpl/resource/123/hierarchy/456/index.php?query=foo",
            "output" => [
                "resourceId" => "123",
                "subItemId" => "456"
            ],
        ], [
            "cArg" => "tpl/resource",
            "input" => "tpl/resource",
            "output" => [],
        ]];

        foreach ($tests as $test) {
            ["cArg" => $cArg, "input" => $input, "output" => $output] = $test;

            $pathTemplateRegex = new TemplateRegex($cArg);
            if ($output === null) {
                $this->assertNull($pathTemplateRegex->match($input));
            } else {
                $this->assertEquals($output, $pathTemplateRegex->match($input));
            }
        }
    }
}
