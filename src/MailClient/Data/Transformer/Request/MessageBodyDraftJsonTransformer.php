<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Data\Transformer\Request;

use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\Core\Contract\JsonDecodable;

/**
 * Interface provides contract for processing data to a MessageBodyDraft.
 *
 * @package Conjoon\MailClient\Data\Protocol\Transformer\Request\Transformer
 */
interface MessageBodyDraftJsonTransformer extends JsonDecodable
{
    /**
     * @inheritdoc
     */
    public static function fromString(string $value): MessageBodyDraft;


    /**
     * @inheritdoc
     */
    public static function fromArray(array $arr): MessageBodyDraft;
}
