<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

use function array_values;
use function assert;
use function sprintf;

use const SEEK_SET;

/**
 * @internal
 */
final class MultipartStream implements MultipartStreamInterface
{
    private const CONTENT_CHUNK_SIZE_IN_BYTES = 1_048_576;

    /** @var non-empty-list<PartOfMultipartStreamInterface> */
    private readonly array $parts;

    private ?int $size = null;

    private bool $bufferCreated = false;

    /**
     * @param non-empty-string $boundary
     */
    public function __construct(
        private readonly StreamInterface $buffer,
        private readonly string $boundary,
        PartOfMultipartStreamInterface ...$parts
    ) {
        Assert::true($this->buffer->isWritable());
        $parts = array_values($parts);
        assert($parts !== []);
        $this->parts = $parts;
    }

    public function getBoundary(): string
    {
        return $this->boundary;
    }

    public function __toString(): string
    {
        return (string) $this->createBuffer();
    }

    public function close(): void
    {
        $this->buffer->close();
    }

    public function detach()
    {
        return $this->buffer->detach();
    }

    public function getSize(): ?int
    {
        $this->createBuffer();
        return $this->size;
    }

    public function tell(): int
    {
        return $this->buffer->tell();
    }

    public function eof(): bool
    {
        return $this->buffer->eof();
    }

    public function isSeekable(): bool
    {
        return $this->buffer->isSeekable();
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->createBuffer()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->buffer->rewind();
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException(sprintf('`%s` is not writable.', self::class));
    }

    public function isReadable(): bool
    {
        return $this->buffer->isReadable();
    }

    public function read($length): string
    {
        return $this->createBuffer()->read($length);
    }

    public function getContents(): string
    {
        return $this->createBuffer()->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->buffer->getMetadata($key);
    }

    private function convertHeadersToString(PartOfMultipartStreamInterface $part): string
    {
        $headers = '';
        foreach ($part->getHeaders() as $headerName => $headerValue) {
            $headers .= sprintf('%s: %s', $headerName, $headerValue) . "\r\n";
        }

        return $headers;
    }

    /**
     * Returns the number of bytes written to the buffer.
     */
    private function passContentToBuffer(StreamInterface $content): int
    {
        if (! $content->isReadable()) {
            return $this->buffer->write((string) $content);
        }

        $written = 0;
        while (! $content->eof()) {
            $written += $this->buffer->write($content->read(self::CONTENT_CHUNK_SIZE_IN_BYTES));
        }

        return $written;
    }

    private function createBuffer(): StreamInterface
    {
        if ($this->bufferCreated) {
            return $this->buffer;
        }

        $this->bufferCreated = true;
        $written             = 0;
        foreach ($this->parts as $part) {
            $written += $this->buffer->write(
                sprintf('--%s', $this->boundary)
                . "\r\n"
                . $this->convertHeadersToString($part)
                . "\r\n"
            );

            $content = $part->getStream();
            if ($content->isSeekable()) {
                $content->rewind();
            }

            $written += $this->passContentToBuffer($content);
            $written += $this->buffer->write("\r\n");
        }

        $written   += $this->buffer->write(sprintf('--%s--', $this->boundary) . "\r\n");
        $this->size = $written;

        return $this->buffer;
    }
}
