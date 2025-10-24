<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ThreeBRSImgproxyExtension extends Extension
{
    public function getAlias(): string
    {
        return 'three_brs_imgproxy';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        // Set configuration parameters
        $container->setParameter('threebrs_imgproxy.base_url', $config['base_url']);
        $container->setParameter('threebrs_imgproxy.source_base_url', $config['source_base_url']);
        $container->setParameter('threebrs_imgproxy.source_prefix', $config['source_prefix']);
        $container->setParameter('threebrs_imgproxy.key', $config['key'] ?? null);
        $container->setParameter('threebrs_imgproxy.salt', $config['salt'] ?? null);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * @param array<int, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
