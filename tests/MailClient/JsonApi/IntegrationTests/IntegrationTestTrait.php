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
use Conjoon\Http\Request;
use Conjoon\Http\RequestMethod;
use Conjoon\Http\StatusCodes;
use Conjoon\JsonProblem\ProblemFactory;
use Conjoon\MailClient\Data\Transformer\Response\JsonApiStrategy as MailClientJsonApiStrategy;
use Conjoon\MailClient\JsonApi\RequestMatcher;
use Conjoon\Net\Url;
use Conjoon\JsonApi\Request as JsonApiRequest;

trait IntegrationTestTrait
{
    protected function getRequestMatcher(): RequestMatcher
    {
        return new RequestMatcher();
    }


    /**
     * @param string $url
     * @return JsonApiRequest|array
     *
     * @throws NotFoundException
     */
    protected function buildJsonApiRequest(string $url): JsonApiRequest|array {
        $url = Url::make($url);

        $httpRequest = new Request($url, RequestMethod::GET);
        $jsonApiRequest = $this->getRequestMatcher()->match($httpRequest);

        if ($jsonApiRequest == null) {
             throw new NotFoundException("https://localhost:8080/rest-api/v1/MailAccounts");
        }

        return $jsonApiRequest;
    }

    protected function getJsonApiStrategy(): MailClientJsonApiStrategy {
        return new MailClientJsonApiStrategy();
    }
}
