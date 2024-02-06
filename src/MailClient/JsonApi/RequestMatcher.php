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

namespace Conjoon\MailClient\JsonApi;

use Conjoon\Http\Exception\NotFoundException;
use Conjoon\Http\Request;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\JsonApi\Request as JsonApiRequest;
use Conjoon\JsonApi\RequestMatcher as JsonApiRequestMatcher;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\Net\Uri\Component\Path\Parameter;
use Conjoon\Net\Uri\Component\Path\ParameterList as PathParameters;
use Conjoon\Net\Uri\Component\Path\Template;

/**
 * Query Validator for MessageItem collection requests.
 *
 */
final class RequestMatcher extends JsonApiRequestMatcher
{
    public const TEMPLATES = [
        MailAccountDescription::class => "MailAccounts",
        MailFolderDescription::class => "MailAccounts/{mailAccountId}/MailFolders"

    ];


    /**
     * @Override
     */
    public function match(Request $request): JsonApiRequest
    {
        foreach(self::TEMPLATES as $descriptionClass => $pathTemplate) {

            $template = new Template($pathTemplate);
            $pathParameters = $template->match($request->getUrl());

            if ($pathParameters !== null) {
                return $this->toJsonApiRequest(
                    $request,
                    $this->toPathParameterList($pathParameters),
                    $descriptionClass
                );
            }

        }

        throw new NotFoundException((string)$request->getUrl());
    }

    private function toJsonApiRequest(Request $request, PathParameters $pathParameters, string $descriptionClass): JsonApiRequest
    {
        return new JsonApiRequest(
            $request,
            $pathParameters,
            new $descriptionClass,
            new QueryValidator()
        );
    }


    private function toPathParameterList(array $parameters): PathParameters
    {
        $pathParameters = new PathParameters();
        foreach ($parameters as $key => $value) {
            $pathParameters[] = new Parameter($key, $value);
        }
        return $pathParameters;
    }
}
