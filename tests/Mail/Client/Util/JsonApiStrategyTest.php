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

namespace Tests\Conjoon\Mail\Client\Util;

use Conjoon\Core\Jsonable;
use Conjoon\Http\Json\Problem\AbstractProblem;
use Conjoon\Mail\Client\Util\JsonApiStrategy;
use Conjoon\Core\Arrayable;
use Conjoon\Core\JsonStrategy;
use Conjoon\Util\AbstractList;
use Tests\TestCase;

/**
 * Class JsonApiStrategyTest
 * @package ests\Conjoon\Mail\Client\Util
 */
class JsonApiStrategyTest extends TestCase
{
    /**
     * Test inheritance
     */
    public function testClass()
    {
        $strategy = new JsonApiStrategy();
        $this->assertInstanceOf(JsonStrategy::class, $strategy);
    }


    /**
     * Test toJson()
     */
    public function testToJson()
    {
        $strategy = new JsonApiStrategy();

        $arrayMock = $this->getMockForAbstractClass(Arrayable::class);
        $arrayMock->expects($this->exactly(6))->method("toArray")->willReturnOnConsecutiveCalls(
            [
                // type missing
                "id" => 1,
                "mailFolderId" => 2,
                "mailAccountId" => 4,
                "attribute_one" => "value",
                "attribute_two" => "value_2"
            ],
            [
            "id" => 1,
            "type" => "Stub",
            "mailFolderId" => 2,
            "mailAccountId" => 4,
            "attribute_one" => "value",
            "attribute_two" => "value_2"
            ],
            [
            "id" => 1,
            "type" => "Stub",
            "mailAccountId" => 4,
            "attribute_one" => "value",
            "attribute_two" => "value_2"
            ],
            [
            "id" => 1,
            "type" => "Stub",
            "attribute_one" => "value",
            "attribute_two" => "value_2"
            ],
            [
                "id" => 1,
                "type" => "MailFolder",
                "attribute_one" => "value",
                "attribute_two" => "value_2",
                "data" => [[
                    "id" => 2,
                    "type" => "MailFolder",
                    "field" => "value"
                ],[
                    "id" => 3,
                    "type" => "MailFolder",
                    "valueFor" => "field"
                ]]
            ],
            [
                "id" => 1,
                "mailFolderId" => "INBOX",
                "mailAccountId" => "dev",
                "type" => "MessageItem",
                "previewText" => "..."
            ]
        );

        $base = [
            "id" => 1,
            "type" => "Stub",
            "attributes" => [
                "attribute_one" => "value",
                "attribute_two" => "value_2"
            ]
        ];

        $results = [
            [
                // no type set
                "id" => 1,
                "mailFolderId" => 2,
                "mailAccountId" => 4,
                "attribute_one" => "value",
                "attribute_two" => "value_2"
            ],
            array_merge($base, [
                "relationships" => [
                    "MailFolder" => [
                        "data" => [
                            "id"   => 2,
                            "type" => "MailFolder"
                        ]
                    ]
                ]
            ]),
            array_merge($base, [
                "relationships" => [
                    "MailAccount" => [
                        "data" => [
                            "id"   => 4,
                            "type" => "MailAccount"
                        ]
                    ]
                ]

            ]),
            $base,
            [
                "id" => 1,
                "type" => "MailFolder",
                "attributes" => [
                    "attribute_one" => "value",
                    "attribute_two" => "value_2",
                    "data" => [
                        [
                            "id" => 2,
                            "type" => "MailFolder",
                            "attributes" => [
                                "field" => "value"
                            ]
                        ], [
                            "id" => 3,
                            "type" => "MailFolder",
                            "attributes" => [
                                "valueFor" => "field"
                            ]
                        ]
                    ]
                ]
            ],

            [
                "id" => 1,
                "type" => "MessageItem",
                "relationships" => [
                    "MailFolder" => [
                        "data" => [
                            "type" => "MailFolder", "id" => "INBOX"
                        ]
                    ]
                ],
                "attributes" => [
                    "previewText" => "..."
                ]
            ]

        ];

        foreach ($results as $result) {
            $this->assertEquals($result, $strategy->toJson($arrayMock));
        }
    }


    /**
     * Tests toJson with a Problem-object
     * @return void
     */
    public function testFromProblem()
    {
        $strategy = new JsonApiStrategy();

        $problemMock = $this->getMockForAbstractClass(
            AbstractProblem::class,
            [],
            '',
            true,
            true,
            true,
            ["toArray"]
        );
        $problemMock->expects($this->exactly(3))->method("toArray")->willReturnOnConsecutiveCalls(
            [
                "title" => "title",
                "status" => 401,
                "detail" => "detail",
                "type" => "type",
                "instance" => "instance"
            ],
            [
                "status" => 401,
                "detail" => "detail",
                "type" => "type"
            ],
            [
                "status" => 401,
                "detail" => "detail",
                "type" => "about:blank"
            ]
        );


        $results = [
            [
                "title"  => "title",
                "status" => 401,
                "detail" => "detail",
                "links"   => [
                    "about" => "type"
                ],
                "meta"   => [
                    "instance" => "instance"
                ]
            ],
            [
                "status" => 401,
                "detail" => "detail",
                "links"   => [
                    "about" => "type"
                ]
            ],
            [
                "status" => 401,
                "detail" => "detail"
            ]
        ];


        foreach ($results as $result) {
            $this->assertEquals($result, $strategy->toJson($problemMock));
        }
    }


    /**
     * Tests toJson with an abstract list
     * @return void
     */
    public function testFromAbstractList()
    {
        $strategy = new JsonApiStrategy();

        $list = $this->getMockForAbstractClass(
            AbstractList::class,
            [],
            '',
            true,
            true,
            true,
            ["toJson"]
        );
        $list->method("getEntityType")->willReturn(Jsonable::class);

        $len = 3;
        for ($i = 0; $i < $len; $i++) {
            $jsonable = $this->getMockBuilder(JsonableMock::class)->onlyMethods(["toJson"])->getMock();
            $jsonable->expects($this->once())->method("toJson")->with($strategy)->willReturn([$i]);
            $list[] = $jsonable;
        }

        $this->assertEquals([[0], [1], [2]], $strategy->toJson($list));
    }
}

/**
 * TestClass
 */
class JsonableMock implements Jsonable, Arrayable
{
    public function toJson(JsonStrategy $strategy = null): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [];
    }
} ;
