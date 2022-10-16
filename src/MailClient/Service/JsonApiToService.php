<?php

namespace Conjoon\MailClient\Service;

use Conjoon\Core\Contract\Jsonable;
use Conjoon\Data\ParameterBag;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\JsonApi\Request\Request as JsonApiRequest;
use Conjoon\JsonProblem\BadRequestProblem;
use Conjoon\JsonProblem\ProblemList as JsonProblemList;

/**
 *
 */
class JsonApiToService
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }


    /**
     * Invokes validating, inspecting and delegating the $request. Return values from the
     * service used with this facade will be returned as Jsonables, so interested clients can
     * pass appropriate JsonStrategies to their toJson-method if required.
     *
     * @param JsonApiRequest $request
     * @return JsonProblemList
     */
    public function invoke(JsonApiRequest $request): Jsonable
    {
        $errors = $request->validate();
        if ($errors->hasError()) {
            return $this->toJsonProblemList();
        }
    }


    // +--------------------------------
    // | Helper
    // +--------------------------------

    /**
     * Converts Validation Errors to JsonProblems, returned as a JsonProblemList.
     *
     * @param ValidationErrors $errors
     * @param JsonApiRequest $request
     *
     * @return JsonProblemList
     */
    protected function toJsonProblemList(
        ValidationErrors $errors,
        JsonApiRequest $request
    ): JsonProblemList {

        $list = new JsonProblemList();
        /**
         * @array<int, ValidationError>
         */
        foreach ($errors as $error) {
            $jsonProblem = new BadRequestProblem(
                title: "Error while validating the query-string",
                detail: $error->getDetails(),
                instance: $request->getUrl(),
            );

            if ($error->getSource() instanceof Parameter) {
                $parameterBag = new ParameterBag();
                $parameterBag->parameter = [
                    "name"  => $error->getSource()->getName(),
                    "value" => $error->getSource()->getValue()
                ];
                $jsonProblem->setAdditionalDetails($parameterBag, null);
            }

            $list[] = $jsonProblem;
        }

        return $list;
    }
}
