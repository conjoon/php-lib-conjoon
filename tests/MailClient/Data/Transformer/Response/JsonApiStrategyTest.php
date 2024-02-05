<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data\Transformer\Response;

use Conjoon\Core\Contract\Jsonable;
use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonProblem\AbstractProblem;
use Conjoon\MailClient\Data\Transformer\Response\JsonApiStrategy;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Core\AbstractList;
use stdClass;
use Tests\TestCase;

/**
 * Tests JsonApiStrategy
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
    public function testFromProblem(): void
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
    public function testFromAbstractList(): void
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


    /**
     * tests fromValidationErrors
     */
    public function testFromValidationErrors()
    {
        $strategy = new JsonApiStrategy();

        $validationErrors = new ValidationErrors();

        $error = $this->createMockForAbstract(
            ValidationError::class,
            ["toArray"],
            [new stdClass(), "details", -1]
        );
        $error->expects($this->once())->method("toArray")->willReturn([
            "fakeError" => ""
        ]);

        $validationErrors[] = $error;

        $result = $validationErrors->toJson($strategy);

        $this->assertEquals(["errors" => [[
            "fakeError" => ""
        ]]], $result);
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
}
