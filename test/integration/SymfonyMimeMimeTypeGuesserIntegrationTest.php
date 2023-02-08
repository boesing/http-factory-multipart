<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Boesing\Psr\Http\Message\Multipart\SymfonyMimeMimeTypeGuesser;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\MimeTypes;

final class SymfonyMimeMimeTypeGuesserIntegrationTest extends TestCase
{
    private SymfonyMimeMimeTypeGuesser $guesser;

    private MimeTypes $mimeTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mimeTypes = new MimeTypes();
        $this->guesser   = new SymfonyMimeMimeTypeGuesser($this->mimeTypes);
    }

    /**
     * @param non-empty-string $filename
     * @dataProvider mimeTypes
     */
    public function testCanDetectMimeType(string $filename, string $mimeType): void
    {
        if (! $this->mimeTypes->isGuesserSupported()) {
            self::markTestSkipped('No guesser is supported and thus mime type detection is not testable.');
        }

        self::assertSame($mimeType, $this->guesser->guessMimeType($filename));
    }

    /**
     * @return Generator<non-empty-string, array{0: non-empty-string, 1: string}>
     */
    public function mimeTypes(): Generator
    {
        yield 'text from file without extension' => [
            __DIR__ . '/resources/textfile',
            'text/plain',
        ];

        yield 'svg from file without extension' => [
            __DIR__ . '/resources/svgfile',
            'image/svg+xml',
        ];

        yield 'text from extension' => [
            'foo.text',
            'text/plain',
        ];

        yield 'svg from extension' => [
            'foo.svg',
            'image/svg+xml',
        ];
    }
}
