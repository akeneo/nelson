<?php

namespace Akeneo\DependencyInjection\Extension;

use Akeneo\DependencyInjection\Configuration\CrowdinConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class CrowdinExtension
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CrowdinExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configurations = $this->processConfiguration(new CrowdinConfiguration(), $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('crowdin.yml');
        $loader->load('services.yml');

        foreach ($configurations as $key => $configuration) {
            $container->setParameter($this->getAlias() . '.' . $key, $configuration);
        }
    }
}
