<?php

namespace Akeneo\DependencyInjection\Extension;

use Akeneo\DependencyInjection\Configuration\GithubConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class GithubExtension
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GithubExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configurations = $this->processConfiguration(new GithubConfiguration(), $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('github.yml');

        foreach ($configurations as $key => $configuration) {
            $container->setParameter($this->getAlias() . '.' . $key, $configuration);
        }
    }
}
