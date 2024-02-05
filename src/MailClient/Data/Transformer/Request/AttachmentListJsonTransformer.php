<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2021-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Data\Transformer\Request;

use Conjoon\MailClient\Message\Attachment\FileAttachmentList;
use Conjoon\Core\Contract\JsonDecodable;

/**
 * Interface AttachmentListJsonTransformer
 * Interface for setting up contracts for converting raw string/array data into a FileAttachmentList.
 *
 *
 * @package Conjoon\MailClient\Data\Protocol\Transformer\Request\Transformer
 */
interface AttachmentListJsonTransformer extends JsonDecodable
{
    /**
     * @inheritdoc
     */
    public static function fromString(string $value): FileAttachmentList;


    /**
     * @inheritdoc
     */
    public static function fromArray(array $arr): FileAttachmentList;
}
