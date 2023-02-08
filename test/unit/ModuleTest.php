<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Boesing\Psr\Http\Message\Multipart\Module;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = new Module();
    }

    public function testWillContainSameConfigurationAsConfigProvider(): void
    {
        $config                            = $this->module->getConfig();
        $expectedConfig                    = (new ConfigProvider())();
        $expectedConfig['service_manager'] = $expectedConfig['dependencies'];
        unset($expectedConfig['dependencies']);

        self::assertEquals($expectedConfig, $config);
    }
}
