<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Override;
use Psr\Http\Message\StreamInterface;

use const SEEK_SET;

final class PartOfMultipartStream implements PartOfMultipartStreamInterface
{
    /**
     * @param non-empty-string $name
     * @param array<non-empty-string,non-empty-string> $headers
     */
    public function __construct(
        private readonly string $name,
        private readonly StreamInterface $stream,
        private readonly string $filename = '',
        private readonly array $headers = []
    ) {
    }

    #[Override]
    public function getFilename(): string
    {
        return $this->filename;
    }

    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[Override]
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return (string) $this->stream;
    }

    #[Override]
    public function close(): void
    {
        $this->stream->close();
    }

    #[Override]
    public function detach()
    {
        return $this->stream->detach();
    }

    #[Override]
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    #[Override]
    public function tell(): int
    {
        return $this->stream->tell();
    }

    #[Override]
    public function eof(): bool
    {
        return $this->stream->eof();
    }

    #[Override]
    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    #[Override]
    public function rewind(): void
    {
        $this->stream->rewind();
    }

    #[Override]
    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    #[Override]
    public function write(string $string): int
    {
        return $this->stream->write($string);
    }

    #[Override]
    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    #[Override]
    public function read(int $length): string
    {
        return $this->stream->read($length);
    }

    #[Override]
    public function getContents(): string
    {
        return $this->stream->getContents();
    }

    #[Override]
    public function getMetadata(?string $key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
