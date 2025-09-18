<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Boesing\Psr\Http\Message\Multipart\ConfigProvider;
use Generator;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function array_keys;
use function array_replace_recursive;
use function class_exists;
use function interface_exists;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigProviderIntegrationTest extends TestCase
{
    use DependenciesFromConfigProviderExtractionTrait;

    /**
     * @return Generator<non-empty-string, array{0: ContainerInterface, 1: non-empty-list<string>}>
     */
    public static function containerProvider(): Generator
    {
        /** @psalm-suppress InvalidArgument Psalm seems to a bug, I might report that once I got some feedback. */
        $dependencies = self::extractDependenciesFromConfigProvider(new ConfigProvider());
        $serviceNames = self::extractServiceNamesFromDependencyConfiguration($dependencies);

        /** @var ServiceManagerConfigurationType $dependenciesFromDiactoros */
        $dependenciesFromDiactoros = (new \Laminas\Diactoros\ConfigProvider())->getDependencies();

        /** @var ServiceManagerConfigurationType $dependencies */
        $dependencies = array_replace_recursive($dependenciesFromDiactoros, $dependencies);

        yield ServiceManager::class => [
            new ServiceManager($dependencies),
            $serviceNames,
        ];
    }

    /**
     * @psalm-param non-empty-list<string> $services
     */
    #[DataProvider('containerProvider')]
    public function testDependenciesCanBeResolvedByContainer(ContainerInterface $container, array $services): void
    {
        foreach ($services as $service) {
            self::assertTrue(class_exists($service) || interface_exists($service));
            self::assertInstanceOf($service, $container->get($service));
        }
    }

    /**
     * @psalm-return non-empty-list<string>
     */
    private static function extractServiceNamesFromDependencyConfiguration(array $dependencies): array
    {
        $factories = $dependencies['factories'] ?? [];
        self::assertIsArray($factories);
        $serviceNames = array_keys($factories);
        self::assertServiceNamesMatchingExpectations($serviceNames);

        return $serviceNames;
    }

    /**
     * @param list<array-key> $serviceNames
     * @psalm-assert non-empty-list<string> $serviceNames
     */
    private static function assertServiceNamesMatchingExpectations(array $serviceNames): void
    {
        self::assertNotEmpty($serviceNames);
        foreach ($serviceNames as $serviceName) {
            self::assertIsString($serviceName);
        }
    }
}
