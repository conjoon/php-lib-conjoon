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

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Data\Sort\SortDirection;
use Conjoon\Data\Sort\SortInfo;
use Conjoon\Data\Sort\SortInfoList;
use Conjoon\MailClient\Data\Resource\DefaultMessageBodyOptions;
use Conjoon\MailClient\Data\Resource\MessageBodyDescription;
use Conjoon\MailClient\Data\Resource\Options;
use Conjoon\MailClient\Data\Resource\OptionsList;
use Conjoon\Mime\MimeType;


class DefaultMessageItemListQuery extends MessageItemListQuery
{

    use QueryTrait;

    public function getStart(): int
    {
        return $this->getInt("page[start]");
    }

    public function getLimit(): int
    {
        return $this->getInt("page[limit]");
    }



    public function getSort(): ?SortInfoList
    {
        $sortField = explode(",", $this->sort ?? "");

        $sortOrderList = new SortInfoList();
        foreach ($sortField as $field) {
            if (!$field) {
                continue;
            }
            $dir = SortDirection::ASC;
            if (str_starts_with($field, "-")) {
                $field = substr($field, 1);
                $dir = SortDirection::DESC;
            }
            $sort = new SortInfo($field, $dir);

            $sortOrderList[] = $sort;
        }

        return $sortOrderList;
    }

    public function getOptions(string $className = null): ?DefaultMessageBodyOptions
    {
        if ($className !== MessageBodyDescription::class) {
            return null;
        }

        $options = $this->availableOptions($className);

        if ($options === null) {
            return null;
        }

        $opts = [];
        foreach ($options as $key => $value) {
                $opts[MimeType::fromString($key)->value] = $value;
        }

        return new DefaultMessageBodyOptions($opts);
    }
}