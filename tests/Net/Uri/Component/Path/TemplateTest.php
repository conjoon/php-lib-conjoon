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

use Conjoon\Core\Contract\Equatable;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Exception\InvalidTypeException;
use Conjoon\Net\Uri\Component\Path\Template;
use Conjoon\Net\Uri;
use Tests\TestCase;

/**
 * tests UriTemplate
 */
class TemplateTest extends TestCase
{
    /**
     * Tests class & types
     * @return void
     */
    public function testClass(): void
    {
        $tpl = new Template("/MailFolder/{mailFolderId}");
        $this->assertInstanceOf(Stringable::class, $tpl);
        $this->assertInstanceOf(Equatable::class, $tpl);
    }

    /**
     * Tests equals() with InvalidTypeException
     * @return void
     */
    public function testEqualsWithException(): void
    {
        $this->expectException(InvalidTypeException::class);

        $tplLft = new Template("/MailFolder/{mailFolderId}");

        $equatable = new class implements Equatable {
            public function equals(object $target): bool
            {
                return true;
            }
        };

        $tplLft->equals($equatable);
    }


    /**
     * Tests equals()
     * @return void
     */
    public function testEquals(): void
    {
        $tplLft = new Template("/MailFolder/{mailFolderId}");
        $tplYes = new Template("/MAILFOLDER/{mailFolderId}");
        $tplNo = new Template("/SnailFolder/{mailFolderId}");

        $this->assertTrue($tplLft->equals($tplYes));
        $this->assertFalse($tplLft->equals($tplNo));
    }

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

            $uriTemplate = new Template($input);
            $this->assertEquals($output, $uriTemplate->getVariableNames());
        }
    }


    /**
     * tests match()
     * @return void
     */
    public function testMatch(): void
    {
        $tpl = new Template("/MailFolder/{mailFolderId}");

        $this->assertSame(
            ["mailFolderId" => "2"],
            $tpl->match(Uri::make("https://localhost:8080/MailFolder/2?query=value"))
        );

        $this->assertNull(
            $tpl->match(Uri::make("https://localhost:8080/path/MailFolder/2?query=value"))
        );

        // relative path
        $tpl = new Template("MailFolder/{mailFolderId}");
        $this->assertSame(
            ["mailFolderId" => "2"],
            $tpl->match(Uri::make("https://localhost:8080/pathMailFolder/2?query=value"))
        );

        $tpl = new Template("/employees");
        $this->assertSame(
            [],
            $tpl->match(Uri::make("https://localhost:8080/employees"))
        );
    }
}
