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

namespace Tests\Conjoon\Net\Uri;

use Conjoon\Net\Uri\PathTemplate;
use Conjoon\Net\Uri;
use Conjoon\Net\Uri\PathTemplateRegex;
use Tests\TestCase;

/**
 * tests UriTemplate
 */
class PathTemplateTest extends TestCase
{
    /**
     * Tests getTemplateString()
     *
     * @return void
     */
    public function testGetPathParameterNames(): void
    {
        $tests = [[
            "input" => "tpl",
            "output" => []
        ], [
            "input" => "/tpl/{resourceId}",
            "output" => [
                "resourceId"
            ]
        ], [
            "input" => "/Resources/{resourceId}",
            "output" => [
                "resourceId"
            ]
        ], [
            "input" => "/tpl/{resourceId}",
            "output" => [
                "resourceId"
            ]
        ], [
            "input" => "tpl/resource/{resourceId}/hierarchy/{subItemId}/subpart",
            "output" => [
                "resourceId", "subItemId"
            ]
        ], [
            "input" => "tpl/resource/{resourceId}/hierarchy/{subItemId}/index.php",
            "output" => [
                "resourceId", "subItemId"
            ],
        ]];

        foreach ($tests as $test) {
            ["input" => $input, "output" => $output] = $test;

            $uriTemplate = new PathTemplate($input);
            $this->assertEquals($output, $uriTemplate->getVariableNames());
        }
    }


    /**
     * tests match()
     * @return void
     */
    public function testMatch(): void
    {
        $tpl = new PathTemplate("/MailFolder/{mailFolderId}");

        $this->assertSame(
            ["mailFolderId" => "2"],
            $tpl->match(Uri::create("https://localhost:8080/MailFolder/2?query=value"))
        );

        $this->assertNull(
            $tpl->match(Uri::create("https://localhost:8080/path/MailFolder/2?query=value"))
        );

        // relative path
        $tpl = new PathTemplate("MailFolder/{mailFolderId}");
        $this->assertSame(
            ["mailFolderId" => "2"],
            $tpl->match(Uri::create("https://localhost:8080/pathMailFolder/2?query=value"))
        );
    }
}
