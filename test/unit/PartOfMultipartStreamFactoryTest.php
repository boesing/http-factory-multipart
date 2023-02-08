<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class PartOfMultipartStreamFactoryTest extends TestCase
{
    private MimeTypeGuesserInterface&MockObject $mimeTypeGuesser;

    private PartOfMultipartStreamFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $this->factory         = new PartOfMultipartStreamFactory($this->mimeTypeGuesser);
    }

    public function testCanCreatePartOfMultipart(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $part   = $this->factory->createPartOfMultipart('foo', $stream);
        self::assertSame($stream, $part->getStream());
        self::assertSame('foo', $part->getName());
    }

    public function testWillCreateContentDispositionHeader(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $part   = $this->factory->createPartOfMultipart('foo', $stream);
        self::assertSame(['content-disposition' => 'form-data; name="foo"'], $part->getHeaders());
    }

    public function testWillCreateContentTypeBasedOnMimeType(): void
    {
        $this->mimeTypeGuesser
            ->expects(self::once())
            ->method('guessMimeType')
            ->willReturn('some/mime');

        $stream  = $this->createMock(StreamInterface::class);
        $part    = $this->factory->createPartOfMultipart('foo', $stream, 'bar');
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-type', $headers);
        self::assertSame('some/mime', $headers['content-type']);
    }

    public function testWillAppendFilenameToContentDisposition(): void
    {
        $stream  = $this->createMock(StreamInterface::class);
        $part    = $this->factory->createPartOfMultipart('foo', $stream, 'bar');
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-disposition', $headers);
        self::assertSame('form-data; name="foo"; filename="bar"', $headers['content-disposition']);
    }

    public function testWillCreateContentLengthHeader(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('getSize')
            ->willReturn(9000);

        $part    = $this->factory->createPartOfMultipart('foo', $stream, 'bar');
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-length', $headers);
        self::assertSame('9000', $headers['content-length']);
    }

    public function testDoesNotOverrideContentDisposition(): void
    {
        $stream  = $this->createMock(StreamInterface::class);
        $part    = $this->factory->createPartOfMultipart('foo', $stream, '', ['Content-Disposition' => 'whatever']);
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-disposition', $headers);
        self::assertSame('whatever', $headers['content-disposition']);
    }

    public function testDoesNotOverrideContentType(): void
    {
        $this->mimeTypeGuesser
            ->expects(self::never())
            ->method('guessMimeType');

        $stream  = $this->createMock(StreamInterface::class);
        $part    = $this->factory->createPartOfMultipart('foo', $stream, 'bar', ['Content-Type' => 'some/mime']);
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-type', $headers);
        self::assertSame('some/mime', $headers['content-type']);
    }

    public function testDoesNotOverrideContentLength(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::never())
            ->method('getSize');

        $part    = $this->factory->createPartOfMultipart('foo', $stream, 'bar', ['Content-Length' => '9000']);
        $headers = $part->getHeaders();
        self::assertArrayHasKey('content-length', $headers);
        self::assertSame('9000', $headers['content-length']);
    }
}
