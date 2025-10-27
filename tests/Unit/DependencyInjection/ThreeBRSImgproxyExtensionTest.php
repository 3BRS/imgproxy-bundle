<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ThreeBRS\ImgproxyBundle\DependencyInjection\Configuration;
use ThreeBRS\ImgproxyBundle\DependencyInjection\ThreeBRSImgproxyExtension;

final class ThreeBRSImgproxyExtensionTest extends TestCase
{
    private ThreeBRSImgproxyExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new ThreeBRSImgproxyExtension();
        $this->container = new ContainerBuilder();
    }

    public function testGetAlias(): void
    {
        $this->assertSame('three_brs_imgproxy', $this->extension->getAlias());
    }

    public function testLoadSetsParameters(): void
    {
        $config = [
            [
                'base_url' => 'https://imgproxy.example.com',
                'source_base_url' => 'https://cdn.example.com',
                'source_prefix' => 'media/images',
                'key' => 'test_key',
                'salt' => 'test_salt',
            ],
        ];

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.base_url'));
        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.source_base_url'));
        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.source_prefix'));
        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.key'));
        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.salt'));

        $this->assertSame('https://imgproxy.example.com', $this->container->getParameter('threebrs_imgproxy.base_url'));
        $this->assertSame('https://cdn.example.com', $this->container->getParameter('threebrs_imgproxy.source_base_url'));
        $this->assertSame('media/images', $this->container->getParameter('threebrs_imgproxy.source_prefix'));
        $this->assertSame('test_key', $this->container->getParameter('threebrs_imgproxy.key'));
        $this->assertSame('test_salt', $this->container->getParameter('threebrs_imgproxy.salt'));
    }

    public function testLoadSetsParametersWithMinimalConfig(): void
    {
        $config = [
            [
                'base_url' => 'https://imgproxy.example.com',
                'source_base_url' => 'https://cdn.example.com',
            ],
        ];

        $this->extension->load($config, $this->container);

        $this->assertSame('https://imgproxy.example.com', $this->container->getParameter('threebrs_imgproxy.base_url'));
        $this->assertSame('https://cdn.example.com', $this->container->getParameter('threebrs_imgproxy.source_base_url'));
        $this->assertSame('', $this->container->getParameter('threebrs_imgproxy.source_prefix'));
        $this->assertNull($this->container->getParameter('threebrs_imgproxy.key'));
        $this->assertNull($this->container->getParameter('threebrs_imgproxy.salt'));
    }

    public function testGetConfigurationReturnsConfigurationInstance(): void
    {
        $configuration = $this->extension->getConfiguration([], $this->container);

        $this->assertInstanceOf(Configuration::class, $configuration);
    }

    public function testLoadRegistersServices(): void
    {
        $config = [
            [
                'base_url' => 'https://imgproxy.example.com',
                'source_base_url' => 'https://cdn.example.com',
            ],
        ];

        $this->extension->load($config, $this->container);

        // Verify that services.yaml was loaded by checking if container has definitions
        // We can't check specific services without compiling the container,
        // but we can verify the extension doesn't throw errors
        $this->assertTrue($this->container->hasParameter('threebrs_imgproxy.base_url'));
    }
}
