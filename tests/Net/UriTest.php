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

namespace Tests\Conjoon\Net;

use BadMethodCallException;
use Conjoon\Net\Exception\UriSyntaxException;
use Conjoon\Net\Uri;
use \Stringable;
use Tests\TestCase;

/**
 * tests Uri
 */
class UriTest extends TestCase
{
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

        /** @phpstan-ignore-next-line */
        $uri->methodDoesNotExist();
    }


    public function testToString(): void
    {
        $this->assertInstanceOf(Stringable::class,$this->createInstanceToTest(["https://host:8080"]));
    }


    /**
     * @return array<int, array<string, string|array<string, int|string>|bool>>
     */
    protected function getTestsForMagicCallToGetters(): array
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


    /**
     * @param array<int, mixed> $arguments
     * @return Uri
     */
    protected function createInstanceToTest(array $arguments): Uri
    {
        $className = $this->getClassToTest();
        /**
         * @var Uri $ret
         */
        $ret =  new $className(...$arguments);
        return $ret;
    }


    protected function getClassToTest(): string
    {
        return Uri::class;
    }
}
