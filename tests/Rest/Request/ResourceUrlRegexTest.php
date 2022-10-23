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

namespace Tests\Conjoon\Rest\Request;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Rest\Request\ResourceUrlRegex;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * tests ResourceUrlRegexList
 */
class ResourceUrlRegexTest extends TestCase
{
    /**
     * @return void
     */
    public function testClass(): void
    {
        $resourceUrlRegex = new ResourceUrlRegex("tpl", "Resource");
        $this->assertInstanceOf(Arrayable::class, $resourceUrlRegex);
        $this->assertSame("tpl", $resourceUrlRegex->getUrlTemplate());
    }

    /**
     * Test prepareIndex()
     * @throws ReflectionException
     */
    public function testPrepareRegex(): void
    {
        $tests = [
            [
                "input" => "/MailAccounts/MailFolders",
                "output" => "/\/MailAccounts\/MailFolders/m"
            ],
            [
                "input" => "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                "output" => "/\/MailAccounts\/(?<mailAccountId>[^\?\/]+)\/MailFolders\/(?<mailFolderId>[^\?\/]+)" .
                            "\/MessageItems(\??[^\/]*$|\/(?<messageItemId>[^\?\/]+))\??[^\/]*$/m"
            ],
            [
                "input" => "/MailAccounts/{mailAccountId}",
                "output" => "/\/MailAccounts(\??[^\/]*$|\/(?<mailAccountId>[^\?\/]+))\??[^\/]*$/m"
            ]
        ];

        foreach ($tests as $test) {
            ["input" => $input, "output" => $output] = $test;

            $resourceUrlRegex = new ResourceUrlRegex($input, "Resource");
            /** @var ReflectionMethod $prepareRegex */
            $prepareRegex = $this->makeAccessible($resourceUrlRegex, "prepareRegex");

            $this->assertSame(
                $output,
                $prepareRegex->invokeArgs($resourceUrlRegex, [])
            );
        }
    }


    /**
     * Tests getMatch()
     * @return void
     */
    public function testGetMatch(): void
    {
        $tests = [
            [
                "input" => ["/MailAccounts/MailFolders", "MailFolders"],
                "args" => ["MailFolders/3"],
                "output" => null
            ],
            [
                "input" => ["/MailAccounts/MailFolders/{mailFolderId}", "MailFolders"],
                "args" => ["https://local.dev/MailAccounts/MailFolders/3"],
                "output" => []
            ],
            [
                "input" => ["/MailAccounts/MailFolders/{mailFolderId}", "MailFolders"],
                "args" => ["https://local.dev/MailAccounts/MailFolders/"],
                "output" => null
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts/MessageItems/53432"],
                "output" => []
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts/MessageItems"],
                "output" => []
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts"],
                "output" => null
            ]
        ];


        foreach ($tests as $test) {
            ["input" => $input, "output" => $output, "args" => $args] = $test;
            $resourceUrlRegex = new ResourceUrlRegex(...$input);

            $match = $resourceUrlRegex->getMatch(...$args);
            if ($output === null) {
                $this->assertNull($match);
            } else {
                $this->assertIsArray($match);
            }
        }
    }

    /**
     * Tests getRegexString()
     * @return void
     */
    public function testGetRegexString(): void
    {
        $urlTemplate = "template";
        $resourceUrlRegex = $this->getMockBuilder(ResourceUrlRegex::class)
                                ->onlyMethods(["prepareRegex"])
                                ->setConstructorArgs([$urlTemplate, "resource"])
                                ->getMock();

        $resourceUrlRegex->expects($this->exactly(2))
                         ->method("prepareRegex")
                         ->willReturn("REGEX");

        $this->assertSame(
            "REGEX",
            $resourceUrlRegex->getRegexString()
        );

        $this->assertSame(
            "REGEX",
            $resourceUrlRegex->getRegexString()
        );
    }


    /**
     * Tests hasResourceId()
     * @return void
     */
    public function testHasResourceId(): void
    {
        $tests = [
            [
                "input" => ["/MailAccounts/MailFolders", "MailFolders"],
                "args" => ["MailFolders/3"],
                "output" => null
            ],
            [
                "input" => ["/MailAccounts/MailFolders/{mailFolderId}", "MailFolders"],
                "args" => ["https://local.dev/MailAccounts/MailFolders/3"],
                "output" => true
            ],
            [
                "input" => ["/MailAccounts/MailFolders/{mailFolderId}", "MailFolders"],
                "args" => ["https://local.dev/MailAccounts/MailFolders/"],
                "output" => null
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts/MessageItems/53432"],
                "output" => true
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts/MessageItems"],
                "output" => false
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["https://localhost/MailAccounts/dev/MailFolders/Inbox.Sent%20Drafts"],
                "output" => null
            ]
        ];


        foreach ($tests as $test) {
            ["input" => $input, "output" => $output, "args" => $args] = $test;

            $resourceUrlRegex = $this->getMockBuilder(ResourceUrlRegex::class)
                                     ->onlyMethods(["getRegexString"])
                                     ->enableProxyingToOriginalMethods()
                                     ->setConstructorArgs($input)
                                     ->getMock();

            $resourceUrlRegex->expects($this->exactly(3))
                             ->method("getRegexString");

            $this->assertSame($output, $resourceUrlRegex->hasResourceId(...$args));
            $orgArg = $args[0];
            // appending query parameter should not change behavior
            $args[0] = $orgArg . "?fields[TYPE]=field1&field2";
            $this->assertSame($output, $resourceUrlRegex->hasResourceId(...$args));

            // adding path separator will always return null
            $args[0] = $orgArg . "error/?fields[TYPE]=field1&field2";
            $this->assertNull($resourceUrlRegex->hasResourceId(...$args));
        }
    }


    /**
     * Tests getPathParameterNames()
     *
     * @return void
     */
    public function testGetPathParameterNames(): void
    {
        $tests = [
            [
                "input" => "/MailAccounts/MailFolders",
                "output" => []
            ],
            [
                "input" => "/MailAccounts/MailFolders/{mailFolderId}",
                "output" => ["mailFolderId"]
            ],
            [
                "input" => "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                "output" => ["mailAccountId", "mailFolderId", "messageItemId"]
            ],

            [
                "input" => "/Mail/{mail}/Accounts/{mailAccountId}/MailFolders/" .
                           "{mailFolderId}/MessageItems/{messageItemId}",
                "output" => ["mail", "mailAccountId", "mailFolderId", "messageItemId"]
            ]
        ];


        foreach ($tests as $test) {
            ["input" => $input, "output" => $output] = $test;
            $resourceUrlRegex = new ResourceUrlRegex($input, "Resource");

            $this->assertSame($output, $resourceUrlRegex->getPathParameterNames());
            // always produces same result
            $this->assertSame($output, $resourceUrlRegex->getPathParameterNames());
        }
    }


    /**
     * Tests getPathParameters()
     *
     * @return void
     */
    public function testGetPathParameters(): void
    {
        $tests = [
            [
                "input" => "/MailAccounts/MailFolders",
                "args" => ["/MailAccounts/MailFolders"],
                "output" => []
            ],
            [
                "input" => "/MailAccounts/MailFolders/{mailFolderId}",
                "args" => ["/MailAccounts/MailFolders/4"],
                "output" => ["mailFolderId" => "4"]
            ],
            [
                "input" => "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                "args" => ["/MailAccounts/dev/MailFolders/Inbox.Drafts%20Sent/MessageItems/randomId"],
                "output" => [
                    "mailAccountId" => "dev", "mailFolderId" => "Inbox.Drafts%20Sent", "messageItemId" => "randomId"
                ]
            ],

            [
                "input" => "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                "args" => ["/MailAccounts/dev/MailFolders/Inbox.Drafts%20Sent/MessageItems"],
                "output" => ["mailAccountId" => "dev", "mailFolderId" => "Inbox.Drafts%20Sent"]
            ]
        ];


        foreach ($tests as $test) {
            ["input" => $input, "args" => $args, "output" => $output] = $test;

            $resourceUrlRegex = $this->getMockProxy([$input, "Resource"]);

            $resourceUrlRegex->expects($this->exactly(2))->method("getMatch");

            $this->assertSame($output, $resourceUrlRegex->getPathParameters(...$args));
            // always produces same result
            $this->assertSame($output, $resourceUrlRegex->getPathParameters(...$args));
        }
    }


    /**
     * Tests getResourceName()
     *
     * @return void
     */
    public function testGetResourceName(): void
    {
        $tests = [
            [
                "input" => ["/MailAccounts/MailFolders", "Resource"],
                "args" => ["/MailAccounts/MailFolders"],
                "output" => "Resource"
            ],
            [
                "input" => ["/MailAccounts/MailFolders/{mailFolderId}", "Resource"],
                "args" => ["/MailAccounts/MailFolders/4"],
                "output" => "Resource"
            ],
            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "Resource"
                ],
                "args" => ["/Mail/4/Accounts/dev/MailFolders/Inbox.Drafts%20Sent/MessageItems/randomId"],
                "output" => null
            ],

            [
                "input" => [
                    "/MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
                    "MessageItem"
                ],
                "args" => ["/MailAccounts/dev/MailFolders/Inbox.Drafts%20Sent/MessageItems"],
                "output" => "MessageItem"
            ]
        ];

        foreach ($tests as $test) {
            ["input" => $input, "args" => $args, "output" => $output] = $test;

            $resourceUrlRegex = $this->getMockProxy($input);
            $resourceUrlRegex->expects($this->exactly(1))->method("getMatch")->with($args[0]);

            $this->assertSame($output, $resourceUrlRegex->getResourceName(...$args));
        }
    }


    /**
     * @return void
     */
    public function testToArray()
    {
        $resourceUrlRegex = new ResourceUrlRegex("/MailAccounts/MailFolders", "Resource");

        $this->assertSame(
            [
                "/MailAccounts/MailFolders", "Resource"
            ],
            $resourceUrlRegex->toArray()
        );
    }


    /**
     * @param array<int, string> $args
     * @return MockObject&ResourceUrlRegex
     */
    protected function getMockProxy(array $args): MockObject&ResourceUrlRegex
    {
        return $this->getMockBuilder(ResourceUrlRegex::class)
            ->onlyMethods(["getMatch"])
            ->enableProxyingToOriginalMethods()
            ->setConstructorArgs($args)
            ->getMock();
    }
}
