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

namespace Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\Filter\Filter;
use Conjoon\JsonApi\Query\Validation\Parameter\ToExpression;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\Math\Expression\Notation\PolishNotationTransformer;


class DefaultMailFolderListQuery extends MailFolderListQuery
{
    /**
     * @Override
     */
    public function getFields(): array {
        $defaultFields = $this->getResourceDescription()->getDefaultFields();

        $relfields = $this->{"relfield:fields[MailFolder]"};
        $fields    = $this->{"fields[MailFolder]"};

        if (!$relfields) {
            return $fields ? explode(",", $fields) : $defaultFields;
        }

        $relfields = explode(",", $relfields);

        foreach ($relfields as $relfield) {
            $prefix    = substr($relfield, 0, 1);
            if ($prefix === "-") {
                $fieldName = substr($relfield, 1);
            }

            if ($prefix === "-") {
                $defaultFields = array_filter($defaultFields, fn ($field) => $field !== $fieldName);
            } else {
                if (!in_array($fieldName, $defaultFields)) {
                    $defaultFields[] = $fieldName;
                }
            }
        }

        return $defaultFields;
    }

    /**
     * @Override
     */
    public function getFilter(): ?Filter {

        return new Filter((new PolishNotationTransformer())->transform(json_decode($this->filter, true)));
    }

}
