<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Psr\Http\Message\StreamInterface;

use const SEEK_SET;

final class PartOfMultipartStream implements PartOfMultipartStreamInterface
{
    /** @var non-empty-string */
    private $name;

    /** @var StreamInterface */
    private $stream;

    /** @var string */
    private $filename;
    /** @var array<non-empty-string,non-empty-string> */
    private $headers;

    /**
     * @param non-empty-string $name
     * @param array<non-empty-string,non-empty-string> $headers
     */
    public function __construct(string $name, StreamInterface $stream, string $filename = '', array $headers = [])
    {
        $this->name     = $name;
        $this->stream   = $stream;
        $this->filename = $filename;
        $this->headers  = $headers;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return (string) $this->stream;
    }

    public function close()
    {
        $this->stream->close();
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getSize()
    {
        return $this->stream->getSize();
    }

    public function tell()
    {
        return $this->stream->tell();
    }

    public function eof()
    {
        return $this->stream->eof();
    }

    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->stream->rewind();
    }

    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    public function write($string)
    {
        return $this->stream->write($string);
    }

    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    public function read($length)
    {
        return $this->stream->read($length);
    }

    public function getContents()
    {
        return $this->stream->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
