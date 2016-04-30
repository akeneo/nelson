<?php

namespace Akeneo\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class GithubConfiguration
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GithubConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        return $builder
            ->root('github')
                ->children()
                    ->scalarNode('token')->isRequired()
                        ->info('Token of the user will generate the Pull Requests')
                    ->end()
                    ->scalarNode('fork_owner')->isRequired()
                        ->info('Name of the user who have forked repository to generate the Pull Requests')
                    ->end()
                    ->scalarNode('owner')->isRequired()
                        ->info('Owner of the repository (example: "akeneo")')
                    ->end()
                    ->scalarNode('repository')->isRequired()
                        ->info('Name of the repository')
                    ->end()
                    ->arrayNode('branches')
                        ->info('List of the branches to pull')
                        ->prototype('scalar')->end()
                        ->defaultValue([null])
                    ->end()
                ->end()
            ->end();
    }
}
