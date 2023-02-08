<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

use function tmpfile;
use function uniqid;

final class MultipartStreamFactoryTest extends TestCase
{
    private MockObject&StreamFactoryInterface $streamFactory;

    private PartOfMultipartStreamFactoryInterface&MockObject $partOfMultipartStreamFactory;

    private MutltipartStreamFactory $multipartStreamFactory;

    private MockObject&StreamInterface $stream;

    protected function setUp(): void
    {
        parent::setUp();
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->stream        = $this->createMock(StreamInterface::class);
        $this->stream
            ->method('isWritable')
            ->willReturn(true);
        $this->streamFactory
            ->method('createStream')
            ->willReturn($this->stream);
        $this->partOfMultipartStreamFactory = $this->createMock(PartOfMultipartStreamFactoryInterface::class);
        $this->multipartStreamFactory       = new MutltipartStreamFactory(
            $this->streamFactory,
            $this->partOfMultipartStreamFactory
        );
    }

    public function testWillDelegateStreamFactoryInterfaceMethods(): void
    {
        $resource = tmpfile();
        self::assertIsResource($resource);

        $streamFromFileMock = $this->createMock(StreamInterface::class);
        $streamFromFileMock
            ->expects(self::never())
            ->method(self::anything());
        $this->streamFactory
            ->expects(self::once())
            ->method('createStreamFromFile')
            ->with('bar', 'w')
            ->willReturn($streamFromFileMock);

        $streamFromResourceMock = $this->createMock(StreamInterface::class);
        $streamFromResourceMock
            ->expects(self::never())
            ->method(self::anything());
        $this->streamFactory
            ->expects(self::once())
            ->method('createStreamFromResource')
            ->with($resource)
            ->willReturn($streamFromResourceMock);

        self::assertSame($this->stream, $this->multipartStreamFactory->createStream('foo'));
        self::assertSame($streamFromFileMock, $this->multipartStreamFactory->createStreamFromFile('bar', 'w'));
        self::assertSame($streamFromResourceMock, $this->multipartStreamFactory->createStreamFromResource($resource));
    }

    public function testWillDetectEmptyParts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');

        $boundary = uniqid();
        self::assertTrue($boundary !== '');
        $this->multipartStreamFactory->createMultipartStream($boundary);
    }

    public function testWillCreateMultipartStream(): void
    {
        $part   = $this->createMock(PartOfMultipartStreamInterface::class);
        $stream = $this->multipartStreamFactory->createMultipartStream('foo', $part);
        self::assertSame('foo', $stream->getBoundary());
    }

    public function testDelegatesCreatePartOfMultipart(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $partOfMultipart = $this->createMock(PartOfMultipartStreamInterface::class);
        $partOfMultipart
            ->expects(self::never())
            ->method(self::anything());

        $this->partOfMultipartStreamFactory
            ->expects(self::once())
            ->method('createPartOfMultipart')
            ->with('name', $stream, 'foo.png', ['bar' => 'baz'])
            ->willReturn($partOfMultipart);

        self::assertSame($partOfMultipart, $this->multipartStreamFactory->createPartOfMultipart('name', $stream, 'foo.png', ['bar' => 'baz']));
    }
}
