<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

use function sprintf;
use function str_replace;
use function uniqid;

final class MultipartStreamIntegrationTest extends TestCase
{
    /** @var non-empty-string */
    private string $boundary;

    private Stream $writableStream;

    public function testWillGenerateExpectedMultipartStream(): void
    {
        $part1 = $this->createMock(PartOfMultipartStreamInterface::class);
        $part1
            ->expects(self::once())
            ->method('getHeaders')
            ->willReturn([]);

        $stream1 = $this->createMock(StreamInterface::class);
        $stream1
            ->expects(self::once())
            ->method('__toString')
            ->willReturn('Stream content');

        $part1
            ->expects(self::once())
            ->method('getStream')
            ->willReturn($stream1);

        $part2 = $this->createMock(PartOfMultipartStreamInterface::class);
        $part2
            ->expects(self::once())
            ->method('getHeaders')
            ->willReturn(['Some-Header' => 'Whatever']);

        $stream2 = $this->createMock(StreamInterface::class);
        $stream2
            ->expects(self::once())
            ->method('__toString')
            ->willReturn('Stream #2 content');

        $part2
            ->expects(self::once())
            ->method('getStream')
            ->willReturn($stream2);

        $stream = new MultipartStream($this->writableStream, $this->boundary, $part1, $part2);

        // Ensure that we do have proper line-endings
        $expectedMultipartStreamContents = str_replace("\n", "\r\n", sprintf(
            <<<'EOT'
            --%1$s
            
            Stream content
            --%1$s
            Some-Header: Whatever
            
            Stream #2 content
            --%1$s--
            
            EOT,
            $this->boundary
        ));
        self::assertSame($expectedMultipartStreamContents, (string) $stream);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $uniqid = uniqid();
        self::assertTrue($uniqid !== '');
        $this->boundary       = $uniqid;
        $this->writableStream = new Stream('php://temp', 'r+');
    }
}
