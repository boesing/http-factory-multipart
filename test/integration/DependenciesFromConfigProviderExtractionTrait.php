<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Laminas\ServiceManager\ConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @see TestCase
 *
 * @psalm-require-extends TestCase
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
trait DependenciesFromConfigProviderExtractionTrait
{
    /**
     * @param callable():array{dependencies:ServiceManagerConfigurationType,...} $configProvider
     */
    private function extractDependenciesFromConfigProvider(callable $configProvider): array
    {
        $config = $configProvider();
        return $config['dependencies'];
    }
}
