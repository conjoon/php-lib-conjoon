<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\JsonApi\IntegrationTests;

use Conjoon\Http\Exception\NotFoundException;
use Conjoon\JsonApi\Exception\BadRequestException;
use Conjoon\JsonApi\Resource\Exception\ResourceNotFoundException;
use Conjoon\JsonApi\Resource\Exception\UnexpectedResolveException;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\Net\Uri\Component\Path\Parameter;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Conjoon\Web\Validation\Exception\ValidationException;
use Tests\TestCase;
use Tests\TestTrait;

class MessageItemTest extends TestCase
{
    use IntegrationTestTrait;
    use TestTrait;

    /**
     * Test NotFoundException.
     *
     * @return void
     */
    public function testNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MaiAccounts/1/MailFolders/123/MessageItems");
    }


    /**
     * Test ValidationException.
     *
     * @return void
     */
    public function testValidationException()
    {
        $this->expectException(ValidationException::class);
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders/2/MessageItems?filter=2");
        $this->assertNotNull($request);

        $resourceResolver = $this->getResourceResolver();
        $resourceResolver->resolve($request);
    }



    /**
     * Test getMessageItems.
     *
     * @return void
     */
    public function testGetMailAccount()
    {
        // /MailAccounts/2/MailFolders - MailAccounts 2 not supposed to exist
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders/2/MessageItems");
        $this->assertNotNull($request);

        $resource = $this->getResourceResolver()->resolve($request);

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertEquals(
            (new Resource($this->getMessageItemList()))->toJson($this->getJsonApiStrategy()),
            $resource->toJson($this->getJsonApiStrategy())
        );
    }


}
