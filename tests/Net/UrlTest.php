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


    /**
     * @return array<int, array<string, mixed>>
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
            "input" => "https://localhost/../path",
            "output" => [
                "scheme" => "https",
                "host" => "localhost",
                "path" => "/../path"
            ],
            "isAbsolute" => true
        ]];
    }


    /**
     * @param array<int, string> $arguments
     * @return Url
     */
    protected function createInstanceToTest(array $arguments): Url
    {
        $className = $this->getClassToTest();
        /**
         * @var Url $ret
         */
        $ret = new $className(...$arguments);
        return $ret;
    }


    protected function getClassToTest(): string
    {
        return Url::class;
    }
}
