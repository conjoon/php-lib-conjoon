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

class MailFolderTest extends TestCase
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
        $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MaiAccounts/1/MailFolderds");
    }


    public function testGetMailFolderUnexpectedResolveException() {
        $this->expectException(UnexpectedResolveException::class);
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders");
        $this->assertNotNull($request);

        $resourceResolver = $this->getResourceResolver();
        $resolveToMailFolder = $this->makeAccessible($resourceResolver, "resolveToMailFolder");

        // force missing pathParameter
        $resolveToMailFolder->invokeArgs($resourceResolver, [new ParameterList(), null]);
    }

    /**
     * Test ValidationException.
     *
     * @return void
     */
    public function testValidationException()
    {
        $this->expectException(ValidationException::class);
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders?include=MailAccount");
        $this->assertNotNull($request);

        $resourceResolver = $this->getResourceResolver();
        $resourceResolver->resolve($request);
    }


    /**
     * Test getMailAccount.
     *
     * @return void
     */
    public function testResourceNotFoundException()
    {
        // /MailAccounts/2/MailFolders - MailAccounts 2 not supposed to exist
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/2/MailFolders");
        $this->assertNotNull($request);

        $this->expectException(ResourceNotFoundException::class);
        $this->getResourceResolver()->resolve($request);
    }

    /**
     * Test getMailAccount.
     *
     * @return void
     */
    public function testGetMailAccount()
    {
        // /MailAccounts/2/MailFolders - MailAccounts 2 not supposed to exist
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders");
        $this->assertNotNull($request);

        $resource = $this->getResourceResolver()->resolve($request);

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertEquals(
            (new Resource($this->getMailFolderChildList()))->toJson($this->getJsonApiStrategy()),
            $resource->toJson($this->getJsonApiStrategy())
        );
    }


}
