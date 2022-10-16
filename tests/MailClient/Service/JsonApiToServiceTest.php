<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\MailClient\Service;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Error\ErrorSource;
use Conjoon\Http\Query\Parameter;
use Conjoon\JsonProblem\BadRequestProblem;
use Conjoon\MailClient\Service\JsonApiToService;
use Conjoon\JsonApi\Request\Request as JsonApiRequest;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\MailClient\Service\ServiceException;
use ReflectionException;
use RuntimeException;
use Tests\TestCase;

/**
 * Test JsonApiToService
 */
class JsonApiToServiceTest extends TestCase
{
    /**
     * Tests toJsonProblemList()
     *
     * @return void
     * @throws ReflectionException
     */
    public function testToJsonProblemList()
    {
        $problematicUrl = "http://problematicurl:8080";

        $request = $this->getMockBuilder(JsonApiRequest::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $validationErrors = new ValidationErrors();
        $error = $this->getMockBuilder(ValidationError::class)->disableOriginalConstructor()->onlyMethods([
            "getSource", "getDetails"
        ])->getMock();
        $validationErrors[] = $error;

        $parameter = $this->getMockBuilder(Parameter::class)->setConstructorArgs(["param", "val"])->getMock();
        $parameter->expects($this->once())->method("getName");
        $parameter->expects($this->once())->method("getValue");
        $error->expects($this->exactly(3))->method("getSource")->willReturn($parameter);
        $error->expects($this->once())->method("getDetails")->willReturn("detailed description");

        $request->expects($this->once())->method("getUrl")->willReturn($problematicUrl);

        $facade = $this->createFacade();
        $toJsonProblemList = $this->makeAccessible($facade, "toJsonProblemList");
        $list = $toJsonProblemList->invokeArgs($facade, [$validationErrors, $request]);

        $this->assertSame(1, count($list));

        $problem = $list[0];
        $this->assertInstanceof(BadRequestProblem::class, $problem);
        $this->assertSame("Error while validating the query-string", $problem->getTitle());
        $this->assertSame("detailed description", $problem->getDetail());
        $this->assertSame($problematicUrl, $problem->getInstance());
        $additionalDetailsKey = $this->makeAccessible($problem, "additionalDetailsKey", true);
        $this->assertNull($additionalDetailsKey->getValue($problem));
    }



    protected function createFacade(): JsonApiToService
    {
        return new JsonApiToService();
    }
}
