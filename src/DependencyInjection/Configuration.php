<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('three_brs_imgproxy');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('base_url')
                    ->info('Imgproxy server URL (e.g. https://imgproxy.example.com or http://localhost:8080)')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('source_base_url')
                    ->info('Base URL for source images (S3/CDN endpoint, e.g. https://bucket.s3.amazonaws.com)')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('source_prefix')
                    ->info('S3 prefix path (e.g. media/image)')
                    ->defaultValue('')
                ->end()
                ->scalarNode('key')
                    ->info('Imgproxy signing key (hex string, optional but recommended for production)')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('salt')
                    ->info('Imgproxy signing salt (hex string, optional but recommended for production)')
                    ->defaultValue(null)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
