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

namespace Conjoon\MailClient\JsonApi\MessageItem;

use Conjoon\JsonApi\PathMatcher;
use Conjoon\Net\Uri;
use Conjoon\Net\Uri\Component\Path\Template;

/**
 * Query Validator for MessageItem collection requests.
 *
 */
final class MessageItemPathMatcher extends PathMatcher
{
    public const TEMPLATE = "MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}";

    private Template $single;

    private Template $collection;

    public function __construct()
    {
        $this->single = new Template(self::TEMPLATE);
        $this->collection = new Template(substr(self::TEMPLATE, 0, strrpos(self::TEMPLATE, "/")));
    }

    public function match(Uri $uri): ?MessageItemPathMatcherResult
    {

        $match = $this->single->match($uri) ?? $this->collection->match($uri);

        if ($match) {
            return new MessageItemPathMatcherResult($match);
        }

        return null;
    }
}
