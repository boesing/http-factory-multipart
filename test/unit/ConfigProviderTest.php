<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Boesing\Psr\Http\Message\Multipart\ConfigProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class ConfigProviderTest extends TestCase
{
    use DependenciesFromConfigProviderExtractionTrait;

    private ConfigProvider $configProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configProvider = new ConfigProvider();
    }

    public function testReturnsExpectedServiceDependencies(): void
    {
        $dependencies = $this->extractDependenciesFromConfigProvider($this->configProvider);

        self::assertArrayHasKey('factories', $dependencies);
        self::assertIsArray($dependencies['factories']);
        self::assertEquals([
            MimeTypeGuesserInterface::class,
            MultipartStreamFactoryInterface::class,
            PartOfMultipartStreamFactoryInterface::class,
        ], array_keys($dependencies['factories']));
    }
}
