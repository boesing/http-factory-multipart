<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

use function strlen;
use function uniqid;

use const SEEK_CUR;

/**
 * @uses MockObject
 */
final class MultipartStreamTest extends TestCase
{
    /** @var non-empty-string */
    private string $boundary;

    private MockObject&StreamInterface $writableStream;

    protected function setUp(): void
    {
        parent::setUp();
        $uniqid = uniqid();
        self::assertTrue($uniqid !== '');
        $this->boundary       = $uniqid;
        $this->writableStream = $this->createMock(StreamInterface::class);
        $this->writableStream
            ->expects(self::any())
            ->method('isWritable')
            ->willReturn(true);
        $this->writableStream
            ->expects(self::any())
            ->method('write')
            ->willReturnCallback(static fn(string $content): int => strlen($content));
    }

    public function testWillReturnPassedBoundary(): void
    {
        $stream = new MultipartStream(
            $this->writableStream,
            $this->boundary,
            $this->createMock(PartOfMultipartStreamInterface::class)
        );
        self::assertSame($this->boundary, $stream->getBoundary());
    }

    public function testIsPsrCompatible(): void
    {
        $stream = new MultipartStream(
            $this->writableStream,
            $this->boundary,
            $this->createMock(PartOfMultipartStreamInterface::class)
        );
        $this->writableStream
            ->expects(self::once())
            ->method('close');
        $stream->close();
        $this->writableStream
        ->expects(self::once())
            ->method('detach');
        $stream->detach();
        $this->writableStream
            ->expects(self::never())
            ->method('getSize');
        self::assertSame(40, $stream->getSize());
        $this->writableStream
            ->expects(self::once())
            ->method('__toString')
            ->willReturn(Stringable::class);
        self::assertSame(Stringable::class, (string) $stream);
        $this->writableStream
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('contents');

        self::assertSame('contents', $stream->getContents());
        $this->writableStream
            ->expects(self::once())
            ->method('tell')
            ->willReturn(1_235_813);
        self::assertSame(1_235_813, $stream->tell());

        $this->writableStream
            ->expects(self::once())
            ->method('eof')
            ->willReturn(true);
        self::assertTrue($stream->eof());

        $this->writableStream
            ->expects(self::once())
            ->method('isSeekable')
            ->willReturn(false);
        self::assertFalse($stream->isSeekable());

        $this->writableStream
            ->expects(self::once())
            ->method('seek')
            ->with(10, SEEK_CUR);
        $stream->seek(10, SEEK_CUR);

        $this->writableStream
            ->expects(self::once())
            ->method('rewind');
        $stream->rewind();

        $this->writableStream
            ->expects(self::never())
            ->method('isWritable');
        self::assertFalse($stream->isWritable());

        $this->writableStream
            ->expects(self::once())
            ->method('isReadable')
            ->willReturn(false);
        self::assertFalse($stream->isReadable());

        $this->writableStream
            ->expects(self::once())
            ->method('read')
            ->with(10)
            ->willReturn('foo');
        self::assertSame('foo', $stream->read(10));

        $this->writableStream
            ->expects(self::once())
            ->method('getMetadata')
            ->with('some key')
            ->willReturn('some value');

        self::assertSame('some value', $stream->getMetadata('some key'));
    }

    public function testMethodsWontWriteToBuffer(): void
    {
        $this->writableStream
            ->expects(self::never())
            ->method('write');
        $this->writableStream
            ->method('tell')
            ->willReturn(0);

        $this->writableStream
            ->method('eof')
            ->willReturn(false);

        $this->writableStream
            ->method('isSeekable')
            ->willReturn(false);

        $this->writableStream
            ->method('isReadable')
            ->willReturn(false);

        $stream = new MultipartStream($this->writableStream, $this->boundary, $this->createMock(PartOfMultipartStreamInterface::class));
        $stream->close();
        $stream->detach();
        $stream->tell();
        $stream->eof();
        $stream->isSeekable();
        $stream->rewind();
        try {
            $stream->isWritable();
        } catch (RuntimeException) {
            /** @psalm-suppress InternalMethod We do actually need this here. */
            self::addToAssertionCount(1);
        }

        $stream->isReadable();
        $stream->getMetadata();
    }
}
