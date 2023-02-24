<?php

namespace Akeneo\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class CrowdinConfiguration
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CrowdinConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('crowdin');

        return $builder->getRootNode()
                ->children()
                    ->integerNode('min_translated_progress')
                        ->defaultValue(0)
                    ->end()
                    ->arrayNode('download')
                        ->isRequired()
                        ->children()
                            ->scalarNode('base_dir')->isRequired()->end()
                            ->scalarNode('valid_locale_pattern')->end()
                            ->arrayNode('locale_map')
                                ->defaultValue([])
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('project')->isRequired()->end()
                    ->scalarNode('key')->isRequired()->end()
                    ->arrayNode('upload')
                        ->isRequired()
                        ->children()
                            ->scalarNode('base_dir')->isRequired()->end()
                        ->end()
                    ->end()
                    ->arrayNode('folders')
                        ->info('List of the folders used for min translation computation')
                        ->prototype('scalar')->end()
                        ->defaultValue([null])
                    ->end()
                ->end()
            ->end();
    }
}
