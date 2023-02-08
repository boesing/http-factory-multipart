<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Exception\ExceptionInterface;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @uses MockObject
 */
final class SymfonyMimeMimeTypeGuesserTest extends TestCase
{
    private MockObject&MimeTypesInterface $mimeTypes;

    private SymfonyMimeMimeTypeGuesser $guesser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mimeTypes = $this->createMock(MimeTypesInterface::class);
        $this->guesser   = new SymfonyMimeMimeTypeGuesser($this->mimeTypes);
    }

    public function testCanHandleUnsupportedGuesser(): void
    {
        $this->mimeTypes
            ->expects(self::once())
            ->method('isGuesserSupported')
            ->willReturn(false);

        self::assertSame('', $this->guesser->guessMimeType('foo'));
    }

    public function testWillDelegateToSymfonyMime(): void
    {
        $this
            ->mimeTypes
            ->expects(self::never())
            ->method('isGuesserSupported');

        $this
            ->mimeTypes
            ->expects(self::once())
            ->method('getMimeTypes')
            ->with('txt')
            ->willReturn(['plain/text']);

        self::assertSame('plain/text', $this->guesser->guessMimeType('foo.txt'));
    }

    public function testCanHandleDelegatedMimeTypeDetectionWithoutAnyMatch(): void
    {
        // The file we are providing does not exist and thus we do not need to bother symfony
        $this
            ->mimeTypes
            ->expects(self::once())
            ->method('isGuesserSupported')
            ->willReturn(true);

        $this
            ->mimeTypes
            ->expects(self::once())
            ->method('getMimeTypes')
            ->with('txt')
            ->willReturn([]);

        self::assertSame('', $this->guesser->guessMimeType('foo.txt'));
    }

    public function testWillDelegateToMimeTypeGuesser(): void
    {
        $this->mimeTypes
            ->expects(self::once())
            ->method('isGuesserSupported')
            ->willReturn(true);

        $this->mimeTypes
            ->expects(self::once())
            ->method('guessMimeType')
            ->willReturn('');

        self::assertSame('', $this->guesser->guessMimeType('foo.txt'));
    }

    public function testCanHandleSymfonyMimeExceptions(): void
    {
        $this->mimeTypes
            ->expects(self::once())
            ->method('isGuesserSupported')
            ->willReturn(true);

        $this
            ->mimeTypes
            ->expects(self::once())
            ->method('guessMimeType')
            ->willThrowException($this->createMock(ExceptionInterface::class));

        self::assertSame('', $this->guesser->guessMimeType('bar.svg'));
    }
}
