<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Webmozart\Assert\Assert;

use function array_values;

final class MutltipartStreamFactory implements MultipartStreamFactoryInterface
{
    public function __construct(
        private readonly StreamFactoryInterface $streamFactory,
        private readonly PartOfMultipartStreamFactoryInterface $partOfMultipartStreamFactory
    ) {
    }

    public function createMultipartStream(
        string $boundary,
        PartOfMultipartStreamInterface ...$parts
    ): MultipartStreamInterface {
        $parts = array_values($parts);
        Assert::isNonEmptyList($parts);
        return new MultipartStream($this->streamFactory->createStream(), $boundary, ...$parts);
    }

    public function createPartOfMultipart(
        string $name,
        StreamInterface $stream,
        string $filename = '',
        array $headers = []
    ): PartOfMultipartStreamInterface {
        return $this->partOfMultipartStreamFactory->createPartOfMultipart($name, $stream, $filename, $headers);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return $this->streamFactory->createStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->streamFactory->createStreamFromFile($filename, $mode);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->streamFactory->createStreamFromResource($resource);
    }
}
