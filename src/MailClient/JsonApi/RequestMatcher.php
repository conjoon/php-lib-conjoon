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

use Conjoon\Http\Request;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\JsonApi\Request as JsonApiRequest;
use Conjoon\JsonApi\RequestMatcher as JsonApiRequestMatcher;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
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
        MailAccountDescription::class => "MailAccounts"
    ];


    public function __construct(
    ) {

    }

    public function match(Request $request) : ?JsonApiRequest {

        $template = new Template(self::TEMPLATES[MailAccountDescription::class]);

        $pathParameters = $template->match($request->getUrl());
        if ($pathParameters !== null) {

            return $this->mailAccountApiRequest(
                $request, $this->toPathParameterList($pathParameters),
            );
        }
        return null;
    }


    private function mailAccountApiRequest(Request $request, PathParameters $pathParameters): JsonApiRequest
    {
        return new JsonApiRequest(
            $request,
            $pathParameters,
            new MailAccountDescription(),
            new QueryValidator()
        );
    }

    private function toPathParameterList(array $parameters): PathParameters {
        $pathParameters = new PathParameters();
        foreach ($parameters as $key => $value) {
            $pathParameters[] = new Parameter($key, $value);
        }
        return $pathParameters;
    }
}