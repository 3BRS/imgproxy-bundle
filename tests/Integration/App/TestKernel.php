<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Integration\App;

use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use ThreeBRS\ImgproxyBundle\ThreeBRSImgproxyBundle;

/**
 * Test kernel for integration tests
 */
class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private array $bundleConfig = [];

    public function __construct(string $environment = 'test', bool $debug = true)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new LiipImagineBundle(),
            new ThreeBRSImgproxyBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__ . '/../../..';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/test';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function setBundleConfig(array $config): void
    {
        $this->bundleConfig = $config;
    }

    protected function configureRoutes($routes): void
    {
        // No routes needed for tests
        // $routes can be RouteCollectionBuilder (Symfony 5.4) or RoutingConfigurator (Symfony 6+)
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        // Make all services public for testing
        $container->setParameter('container.dumper.inline_class_loader', true);
        $container->setParameter('container.dumper.inline_factories', false);

        // Framework configuration
        $container->loadFromExtension('framework', [
            'secret' => 'test_secret',
            'test' => true,
            'default_locale' => 'en',
            'router' => [
                'utf8' => true,
            ],
            'http_method_override' => false,
            'profiler' => [
                'enabled' => false,
            ],
        ]);

        // Liip Imagine Bundle configuration
        $container->loadFromExtension('liip_imagine', [
            'resolvers' => [
                'default' => [
                    'web_path' => [],
                ],
            ],
            'loaders' => [
                'default' => [
                    'filesystem' => [
                        'data_root' => '%kernel.project_dir%/tests/Fixtures/images',
                    ],
                ],
            ],
            'driver' => 'gd',
            'cache' => 'default',
            'data_loader' => 'default',
            'default_image' => null,
            'filter_sets' => [
                'thumbnail' => [
                    'quality' => 85,
                    'filters' => [
                        'thumbnail' => [
                            'size' => [300, 200],
                            'mode' => 'outbound',
                        ],
                    ],
                ],
                'small' => [
                    'quality' => 90,
                    'filters' => [
                        'thumbnail' => [
                            'size' => [150, 150],
                            'mode' => 'inset',
                        ],
                    ],
                ],
            ],
        ]);

        // Imgproxy Bundle configuration
        if ($this->bundleConfig !== []) {
            $container->loadFromExtension('three_brs_imgproxy', $this->bundleConfig);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        // Provide a dummy database_connection service if it's needed
        if (!$container->has('database_connection')) {
            $container->register('database_connection', \stdClass::class)
                ->setPublic(true);
        }

        // Make all services public for testing
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $definition) {
                    $definition->setPublic(true);
                }

                foreach ($container->getAliases() as $alias) {
                    $alias->setPublic(true);
                }
            }
        });

        // Fix for TestServiceContainerRealRefPass - remove invalid aliases
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definitions = $container->getDefinitions();
                foreach ($container->getAliases() as $id => $alias) {
                    $target = (string) $alias;
                    while ($container->hasAlias($target)) {
                        $target = (string) $container->getAlias($target);
                    }
                    // Remove alias if target definition doesn't exist
                    if (!isset($definitions[$target])) {
                        $container->removeAlias($id);
                    }
                }
            }
        }, PassConfig::TYPE_AFTER_REMOVING, 1); // Run before TestServiceContainerRealRefPass
    }
}
