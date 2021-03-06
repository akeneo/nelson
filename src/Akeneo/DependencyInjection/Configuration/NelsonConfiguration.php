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
class NelsonConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        return $builder
            ->root('nelson')
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
                    ->scalarNode('log_file')
                        ->info('File where logs are written. Default: app/logs/application.log')
                        ->defaultValue('app/logs/application.log')
                    ->end()
                    ->scalarNode('log_locale')
                        ->info('Locale for log display.')
                        ->defaultValue('en_US')
                    ->end()
                ->end()
            ->end();
    }
}
