<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Laminas\ServiceManager\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigProvider
{
    /**
     * @return array{dependencies: ServiceManagerConfigurationType,...}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getServiceDependencies(),
        ];
    }

    /**
     * @return ServiceManagerConfigurationType
     */
    private function getServiceDependencies(): array
    {
        return [
            'factories' => [
                MimeTypeGuesserInterface::class              => static fn(): MimeTypeGuesserInterface => new SymfonyMimeMimeTypeGuesser(new MimeTypes()),
                MultipartStreamFactoryInterface::class       => static fn(ContainerInterface $container): MultipartStreamFactoryInterface => new MutltipartStreamFactory(
                    $container->get(StreamFactoryInterface::class),
                    $container->get(PartOfMultipartStreamFactoryInterface::class)
                ),
                PartOfMultipartStreamFactoryInterface::class => static fn(ContainerInterface $container): PartOfMultipartStreamFactoryInterface => new PartOfMultipartStreamFactory($container->get(MimeTypeGuesserInterface::class)),
            ],
        ];
    }
}
