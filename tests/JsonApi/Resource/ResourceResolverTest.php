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

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceCollection;
use Conjoon\JsonApi\Resource\ResourceList;
use Conjoon\JsonApi\Resource\ResourceResolver;
use Conjoon\Net\Uri;
use Conjoon\Net\Uri\Component\Path\Template;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Tests Request
 */
class ResourceResolverTest extends TestCase
{
    /**
     * tests resolve()
     */
    public function testResolve(): void
    {
        $resource = fn (string $uri, string $name) => $this->createResource($uri, $name);
        $collection = fn (string $uri, string $name) => $this->createResourceCollection($uri, $name);


        $resources = [
            "employeeResource" => $resource("/employees/{id}", "Employee"),
            "employeeCollection" => $collection("/employees", "Employee"),
            "shippingResource" => $resource("/employees/{employeeId}/shippingAddresses/{id}", "ShippingAddress"),
            "shippingCollection" => $collection("/employees/{employeeId}/shippingAddresses", "ShippingAddress")
        ];

        $resolver = new ResourceResolver(ResourceList::make(...array_values($resources)));

        $tests = [[
            "input" => "https://localhost:8080/employees/123",
            "output" => "employeeResource"
        ], [
            "input" => "https://localhost:8080/employees",
            "output" => "employeeCollection"
        ], [
            "input" => "/employees",
            "output" => "employeeCollection"
        ], [
            "input" => "https://localhost:8080/employees/123/shippingAddresses",
            "output" => "shippingCollection"
        ], [
            "input" => "https://localhost:8080/employees/123/shippingAddresses/2",
            "output" => "shippingResource"
        ]];

        foreach ($tests as $test) {
            ["input" => $input, "output" => $output] = $test;
            $this->assertSame(
                $resources[$output],
                $resolver->resolve(Uri::make($input))
            );
        }
    }


    /**
     * tests resolve with Employee Resources
     * @return void
     */
    public function testResolveWithoutMocks(): void
    {
        $employeeResource = new EmployeeResource();
        $employeeResourceCollection = new EmployeeResourceCollection();

        $resources = [
            $employeeResource,
            $employeeResourceCollection
        ];

        $resolver = new ResourceResolver(ResourceList::make(...$resources));

        $this->assertSame(
            $employeeResource,
            $resolver->resolve(Uri::make("https://localhost:8080/directory/employees/1"))
        );

        $this->assertSame(
            $employeeResourceCollection,
            $resolver->resolve(Uri::make("https://localhost:8080/directory/employees"))
        );

        $this->assertNull(
            $resolver->resolve(Uri::make("https://localhost:8080/directory/shipments"))
        );
    }


    private function createResource(string $uri, string $entityName): MockObject&Resource
    {
        $mock = $this->getMockBuilder(Resource::class)
            ->onlyMethods(["getUri"])
            ->getMock();

        $mock->expects($this->any())->method("getUri")->willReturn(new Template($uri));

        return $mock;
    }


    private function createResourceCollection(string $uri, string $entityName): MockObject&ResourceCollection
    {
        $mock = $this->getMockBuilder(ResourceCollection::class)
            ->onlyMethods(["getUri"])
            ->getMock();

        $mock->expects($this->any())->method("getUri")->willReturn(new Template($uri));


        return $mock;
    }
}

class EmployeeResource implements Resource
{
    public function getUri(): Template
    {
        return new Template("/directory/employees/{id}");
    }
}

class EmployeeResourceCollection extends EmployeeResource implements ResourceCollection
{
    public function getUri(): Template
    {
        return new Template("/directory/employees");
    }
}
