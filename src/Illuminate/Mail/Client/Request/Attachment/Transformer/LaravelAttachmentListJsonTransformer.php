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

namespace Conjoon\Illuminate\Mail\Client\Request\Attachment\Transformer;

use Conjoon\Mail\Client\Attachment\FileAttachment;
use Conjoon\Mail\Client\Attachment\FileAttachmentList;
use Conjoon\Mail\Client\Request\Attachment\Transformer\AttachmentListJsonTransformer;
use RuntimeException;

/**
 * Class LaravelAttachmentListJsonTransformer
 * @package Conjoon\Illuminate\Mail\Client\Request\Attachment\Transformer
 */
class LaravelAttachmentListJsonTransformer implements AttachmentListJsonTransformer
{
    /**
     * @inheritdoc
     * We don't support fromString right now
     */
    public static function fromString(string $value): FileAttachmentList
    {
        throw new RuntimeException("\"fromString\" is not supported");
    }


    /**
     *
     * @param array[] $arr A numeric array with the following key value pairs:
     *  - string $text Arbitrary text to use as a description for the file, e.g. the file name
     *  - string $type The mime type of the file
     *  - string $mailAccountId The id of the MailAccount of the owning message
     *  - string $mailFolderId The id of the mailFolder of the owning message
     *  - string $parentMessageItemId The id of the owining message
     *  - Illuminate\Transformer\UploadedFile $blob The data of the FileInput as sent by the client
     *
     * @inheritdoc
     */
    public static function fromArray(array $arr): FileAttachmentList
    {
        $list = new FileAttachmentList();

        foreach ($arr as $file) {
            $uploadedFile = $file["blob"];

            $transfer = ["text" => $file["text"]];
            $transfer["type"] = $uploadedFile->getMimeType();
            $transfer["content"] = base64_encode($uploadedFile->get());
            $transfer["size"] = mb_strlen($transfer["content"], "8bit");
            // encoding is usually only of interest when sending the file back to the client.
            $transfer["encoding"] = "base64";

            $att = new FileAttachment($transfer);
            $list[] = $att;
        }

        return $list;
    }
}
