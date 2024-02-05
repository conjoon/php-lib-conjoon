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

namespace Tests\Conjoon\Illuminate\MailClient\Data\Protocol\Http\Request\Transformer;

use Conjoon\Illuminate\MailClient\Data\Protocol\Http\Request\Transformer\LaravelAttachmentListJsonTransformer;
use Conjoon\MailClient\Message\Attachment\FileAttachmentList;
use Conjoon\MailClient\Data\Transformer\Request\AttachmentListJsonTransformer;
use Conjoon\Core\Contract\JsonDecodable;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

/**
 * Class LaravelAttachmentListJsonTransformerTest
 * @package Tests\Conjoon\Illuminate\MailClient\Data\Protocol\Transformer\Request\Transformer
 */
class LaravelAttachmentListJsonTransformerTest extends TestCase
{
    /**
     * Test inheritance
     */
    public function testClass()
    {

        $transformer = new LaravelAttachmentListJsonTransformer();
        $this->assertInstanceOf(AttachmentListJsonTransformer::class, $transformer);
        $this->assertInstanceOf(JsonDecodable::class, $transformer);
    }


    /**
     * Test fromArray
     */
    public function testFromArray()
    {
        $transformer = new LaravelAttachmentListJsonTransformer();

        $testFiles = [
            __DIR__ . "/Fixtures/testFile.txt",
            __DIR__ . "/Fixtures/image.png"
        ];

        $files = [[
            "text" => "file1",
            "type" => "client/type",
            "blob" => new UploadedFile($testFiles[0], "clientFilename", null, null, true)
        ], [
            "text" => "file2",
            "type" => "client/type",
            "blob" => new UploadedFile($testFiles[1], "clientFilename", null, null, true)
        ]];

        $result = [[
            "text" => $files[0]["text"],
            "type" => "text/plain",
            "encoding" => "base64",
            "size" => mb_strlen(base64_encode(file_get_contents($testFiles[0])), "8bit"),
            "content" => base64_encode(file_get_contents($testFiles[0]))
        ], [
            "text" => $files[1]["text"],
            "type" => "image/png",
            "encoding" => "base64",
            "size" => mb_strlen(base64_encode(file_get_contents($testFiles[1])), "8bit"),
            "content" => base64_encode(file_get_contents($testFiles[1]))
        ]];

        $fileList = $transformer::fromArray($files);

        $this->assertSame(count($fileList), 2);

        $i = 0;
        foreach ($fileList as $file) {
            $this->assertSame($result[$i]["size"], $file->getSize());
            $this->assertSame($result[$i]["type"], $file->getType());
            $this->assertSame($result[$i]["text"], $file->getText());
            $this->assertSame($result[$i]["encoding"], $file->getEncoding());
            $this->assertSame($result[$i]["content"], $file->getContent());
            $i++;
        }

        $this->assertInstanceOf(FileAttachmentList::class, $fileList);
    }


    /**
     * Test fromString
     */
    public function testFromString()
    {
        $this->expectException(RuntimeException::class);

        $transformer = new LaravelAttachmentListJsonTransformer();

        $transformer::fromString(json_encode([]));
    }
}
