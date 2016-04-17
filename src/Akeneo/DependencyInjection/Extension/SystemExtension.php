<?php

namespace Akeneo\DependencyInjection\Extension;

use Akeneo\DependencyInjection\Configuration\SystemConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class SystemExtension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SystemExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configurations = $this->processConfiguration(new SystemConfiguration(), $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('system.yml');

        foreach ($configurations as $key => $configuration) {
            $container->setParameter($this->getAlias() . '.' . $key, $configuration);
        }
    }
}
