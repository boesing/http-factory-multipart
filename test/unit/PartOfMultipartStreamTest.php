<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Stringable;

use function assert;
use function bin2hex;
use function random_bytes;

use const SEEK_CUR;

final class PartOfMultipartStreamTest extends TestCase
{
    /** @var MockObject&StreamInterface */
    private $stream;

    /** @var non-empty-string */
    private $name;

    /** @var PartOfMultipartStream */
    private $part;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stream = $this->createMock(StreamInterface::class);
        $name         = bin2hex(random_bytes(10));
        assert($name !== '');
        $this->name = $name;
        $this->part = new PartOfMultipartStream($this->name, $this->stream, '', []);
    }

    public function testWillReturnExpectedName(): void
    {
        self::assertSame($this->name, $this->part->getName());
    }

    public function testWillReturnExpectedStream(): void
    {
        self::assertSame($this->stream, $this->part->getStream());
    }

    public function testWontAppendAnyHeaders(): void
    {
        self::assertSame([], $this->part->getHeaders());
    }

    public function testWontCallAnythingOnStreamWhenInstantiating(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::never())
            ->method(self::anything());

        new PartOfMultipartStream($this->name, $stream);
    }

    public function testDelegatesPsrMethodsToStream(): void
    {
        $this->stream
            ->expects(self::once())
            ->method('close');
        $this->part->close();
        $this->stream
            ->expects(self::once())
            ->method('detach');
        $this->part->detach();
        $this->stream
            ->expects(self::once())
            ->method('getSize')
            ->willReturn(42);
        self::assertSame(42, $this->part->getSize());
        $this->stream
            ->expects(self::once())
            ->method('__toString')
            ->willReturn(Stringable::class);
        self::assertSame(Stringable::class, (string) $this->part);
        $this->stream
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('contents');

        self::assertSame('contents', $this->part->getContents());
        $this->stream
            ->expects(self::once())
            ->method('tell')
            ->willReturn(1235813);
        self::assertSame(1235813, $this->part->tell());

        $this->stream
            ->expects(self::once())
            ->method('eof')
            ->willReturn(true);
        self::assertTrue($this->part->eof());

        $this->stream
            ->expects(self::once())
            ->method('isSeekable')
            ->willReturn(false);
        self::assertFalse($this->part->isSeekable());

        $this->stream
            ->expects(self::once())
            ->method('seek')
            ->with(10, SEEK_CUR);
        $this->part->seek(10, SEEK_CUR);

        $this->stream
            ->expects(self::once())
            ->method('rewind');
        $this->part->rewind();

        $this->stream
            ->expects(self::once())
            ->method('isWritable')
            ->willReturn(false);
        self::assertFalse($this->part->isWritable());

        $this->stream
            ->expects(self::once())
            ->method('isReadable')
            ->willReturn(false);
        self::assertFalse($this->part->isReadable());

        $this->stream
            ->expects(self::once())
            ->method('read')
            ->with(10)
            ->willReturn('foo');
        self::assertSame('foo', $this->part->read(10));

        $this->stream
            ->expects(self::once())
            ->method('getMetadata')
            ->with('some key')
            ->willReturn('some value');

        self::assertSame('some value', $this->part->getMetadata('some key'));
    }
}
