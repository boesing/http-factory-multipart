<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Boesing\Psr\Http\Message\Multipart\MimeTypeGuesserInterface;
use Psr\Http\Message\StreamInterface;
use Webmozart\Assert\Assert;

use function basename;
use function is_string;
use function sprintf;
use function str_starts_with;
use function strtolower;

final class PartOfMultipartStreamFactory implements PartOfMultipartStreamFactoryInterface
{
    public function __construct(
        private readonly MimeTypeGuesserInterface $mimeTypeGuesser
    ) {
    }

    public function createPartOfMultipart(
        string $name,
        StreamInterface $stream,
        string $filename = '',
        array $headers = []
    ): PartOfMultipartStreamInterface {
        $filename = $this->detectFilenameBasename($filename, $stream);
        $headers  = $this->normalizeAndExtendHeaders($name, $stream, $filename, $headers);

        return new PartOfMultipartStream($name, $stream, $filename, $headers);
    }

    /**
     * @param non-empty-string $name
     * @param array<non-empty-string,non-empty-string> $headers
     * @return array<non-empty-string,non-empty-string>
     */
    private function normalizeAndExtendHeaders(string $name, StreamInterface $stream, string $filename, array $headers): array
    {
        $normalized = [];
        foreach ($headers as $headerName => $headerValue) {
            $headerName              = strtolower($headerName);
            $normalized[$headerName] = $headerValue;
        }

        $normalized = $this->extendHeadersWithContentDispositionHeader($normalized, $name, $filename);
        $normalized = $this->extendHeadersWithContentTypeHeader($normalized, $filename);
        return $this->extendHeadersWithContentLengthHeader($normalized, $stream);
    }

    private function detectFilenameBasename(string $filename, StreamInterface $stream): string
    {
        if ($filename !== '') {
            return basename($filename);
        }

        $uriFromStream = $stream->getMetadata('uri');
        if (! is_string($uriFromStream) || str_starts_with($uriFromStream, 'php://')) {
            return '';
        }

        return basename($uriFromStream);
    }

    /**
     * @param array<non-empty-string,non-empty-string> $normalizedHeaders
     * @param non-empty-string $name
     * @return array<non-empty-string,non-empty-string>
     */
    private function extendHeadersWithContentDispositionHeader(array $normalizedHeaders, string $name, string $filename): array
    {
        if (isset($normalizedHeaders['content-disposition'])) {
            return $normalizedHeaders;
        }

        $normalizedHeaders['content-disposition'] = sprintf('form-data; name="%s"', $name);

        if ($filename !== '') {
            $normalizedHeaders['content-disposition'] .= sprintf('; filename="%s"', $filename);
        }

        Assert::allStringNotEmpty($normalizedHeaders);
        return $normalizedHeaders;
    }

    /**
     * @param array<non-empty-string,non-empty-string> $normalizedHeaders
     * @return array<non-empty-string,non-empty-string>
     */
    private function extendHeadersWithContentTypeHeader(array $normalizedHeaders, string $filename): array
    {
        if ($filename === '' || isset($normalizedHeaders['content-type'])) {
            return $normalizedHeaders;
        }

        $mimeType = $this->mimeTypeGuesser->guessMimeType($filename);
        if ($mimeType === '') {
            return $normalizedHeaders;
        }

        $normalizedHeaders['content-type'] = $mimeType;
        return $normalizedHeaders;
    }

    /**
     * @param array<non-empty-string,non-empty-string> $normalizedHeaders
     * @return array<non-empty-string,non-empty-string>
     */
    private function extendHeadersWithContentLengthHeader(array $normalizedHeaders, StreamInterface $stream): array
    {
        if (isset($normalizedHeaders['content-length'])) {
            return $normalizedHeaders;
        }

        $size = $stream->getSize();
        if ($size === null) {
            return $normalizedHeaders;
        }
        Assert::allStringNotEmpty($normalizedHeaders);
        $normalizedHeaders['content-length'] = (string) $size;

        return $normalizedHeaders;
    }
}
