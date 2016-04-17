<?php

namespace Akeneo\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class SystemConfiguration
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SystemConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        return $builder
            ->root('system')
                ->children()
                    ->arrayNode('finder_options')
                        ->info('Functions to apply to finder to select original files')
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                    ->end()
                    ->arrayNode('target_rules')
                        ->info('Regular expression to generate target from path')
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                    ->end()
                    ->scalarNode('pattern_suffix')
                        ->info('Add folder to pattern when download archive')
                        ->defaultValue(null)
                    ->end()
                ->end()
            ->end();
    }
}
