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

use Conjoon\Http\Exception\BadRequestException;
use Conjoon\Http\Exception\NotFoundException;
use Conjoon\Http\Request;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\JsonApi\Request as JsonApiRequest;
use Conjoon\JsonApi\RequestMatcher as JsonApiRequestMatcher;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\JsonApi\Query\MessageItemListQueryValidator;
use Conjoon\Net\Uri\Component\Path\Parameter;
use Conjoon\Net\Uri\Component\Path\ParameterList as PathParameters;
use Conjoon\Net\Uri\Component\Path\Template;
use Conjoon\MailClient\JsonApi\Query\MailAccountListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailFolderListQueryValidator;

/**
 * Query Validator for MessageItem collection requests.
 *
 */
final class RequestMatcher extends JsonApiRequestMatcher
{
    public const TEMPLATES = [
        MailAccountDescription::class => "MailAccounts",
        MailFolderDescription::class => "MailAccounts/{mailAccountId}/MailFolders",
        MessageItemDescription::class => "MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems"
    ];

    public const VALIDATORS = [
        MailAccountDescription::class => MailAccountListQueryValidator::class,
        MailFolderDescription::class => MailFolderListQueryValidator::class,
        MessageItemDescription::class => MessageItemListQueryValidator::class
    ];


    /**
     * @Override
     */
    public function match(Request $request): JsonApiRequest
    {
        if ($request->getHeader("Accept") != "application/vnd.api+json;ext=\"https://conjoon.org/json-api/ext/relfield\"") {
            throw new BadRequestException(
                "missing \"Accept\"-Header with value " .
                "`application/vnd.api+json;ext=\"https://conjoon.org/json-api/ext/relfield\"`");
        }

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

    private function toJsonApiRequest(
        Request $request,
        PathParameters $pathParameters,
        string $descriptionClass
    ): JsonApiRequest
    {
        return new JsonApiRequest(
            $request,
            $pathParameters,
            new $descriptionClass,
            $this->toQueryValidator($descriptionClass)
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

    private function toQueryValidator(string $className): QueryValidator {
        if (array_key_exists($className, self::VALIDATORS)) {
            $queryClass = self::VALIDATORS[$className];
            return new $queryClass();
        }

        return new QueryValidator();
    }
}
