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
use Conjoon\MailClient\Data\Resource\MailFolderListOptions;
use Conjoon\MailClient\Data\Resource\DefaultMailFolderListOptions;
use Conjoon\Math\Expression\Notation\PolishNotationTransformer;


class DefaultMailFolderListQuery extends MailFolderListQuery
{
    use QueryTrait;

    public function getOptions(): ?MailFolderListOptions {

        if ($this->{"options[MailFolder]"}) {
            $options = json_decode($this->{"options[MailFolder]"}, true);
            return new DefaultMailFolderListOptions($options["dissolveNamespaces"] ?? []);
        }

        return null;
    }

    /**
     * @Override
     */
    public function getFilter(): ?Filter {
        return new Filter((new PolishNotationTransformer())->transform(json_decode($this->filter, true)));
    }

}
