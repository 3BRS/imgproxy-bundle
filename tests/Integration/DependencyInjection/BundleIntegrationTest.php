<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Integration\DependencyInjection;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use ThreeBRS\ImgproxyBundle\Imagine\Cache\Resolver\ImgproxyCacheResolver;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverter;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverterInterface;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilder;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilderInterface;
use ThreeBRS\ImgproxyBundle\Tests\Integration\IntegrationTestCase;

/**
 * Tests bundle loading and DI container configuration
 */
final class BundleIntegrationTest extends IntegrationTestCase
{
    public function testBundleBootsSuccessfully(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $this->assertNotNull($this->kernel);
        $this->assertNotNull($this->container);
    }

    public function testBundleRegistersAllServices(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        // Check URL builder
        $this->assertTrue($this->hasService(ImgproxyUrlBuilderInterface::class));
        $this->assertTrue($this->hasService(ImgproxyUrlBuilder::class));

        // Check filter converter
        $this->assertTrue($this->hasService(FilterConfigConverterInterface::class));
        $this->assertTrue($this->hasService(FilterConfigConverter::class));

        // Check cache resolver
        $this->assertTrue($this->hasService(ImgproxyCacheResolver::class));
    }

    public function testUrlBuilderServiceIsConfiguredCorrectly(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'source_prefix' => 'media/images',
            'key' => '943b421c9eb07c830af81030552c86009268de4e532ba2ee2eab8247c6da0881',
            'salt' => '520f986b998545b4785e0defbc4f3c1203f22de2374a3d53cb7a7fe9fea309c5',
        ];

        $this->bootKernel($config);

        $urlBuilder = $this->getService(ImgproxyUrlBuilderInterface::class);

        $this->assertInstanceOf(ImgproxyUrlBuilder::class, $urlBuilder);

        // Test that configuration was applied by generating a URL
        $url = $urlBuilder->build('test.jpg', ['width' => 300, 'height' => 200]);

        $this->assertStringContainsString('https://imgproxy.example.com', $url);
    }

    public function testFilterConverterServiceHasAccessToFilterConfiguration(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $converter = $this->getService(FilterConfigConverterInterface::class);

        $this->assertInstanceOf(FilterConfigConverter::class, $converter);

        // Test that converter has access to Liip filter configuration
        $options = $converter->convert('thumbnail');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('width', $options);
        $this->assertArrayHasKey('height', $options);
        $this->assertSame(300, $options['width']);
        $this->assertSame(200, $options['height']);
    }

    public function testCacheResolverServiceIsConfiguredCorrectly(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        $this->assertInstanceOf(ImgproxyCacheResolver::class, $resolver);
        $this->assertInstanceOf(ResolverInterface::class, $resolver);
    }

    public function testConfigurationParametersAreSetCorrectly(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'source_prefix' => 'uploads/images',
            'key' => 'my_key',
            'salt' => 'my_salt',
        ];

        $this->bootKernel($config);

        $this->assertSame('https://imgproxy.example.com', $this->getParameter('threebrs_imgproxy.base_url'));
        $this->assertSame('https://cdn.example.com', $this->getParameter('threebrs_imgproxy.source_base_url'));
        $this->assertSame('uploads/images', $this->getParameter('threebrs_imgproxy.source_prefix'));
        $this->assertSame('my_key', $this->getParameter('threebrs_imgproxy.key'));
        $this->assertSame('my_salt', $this->getParameter('threebrs_imgproxy.salt'));
    }

    public function testMinimalConfigurationWorks(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $this->assertSame('https://imgproxy.example.com', $this->getParameter('threebrs_imgproxy.base_url'));
        $this->assertSame('https://cdn.example.com', $this->getParameter('threebrs_imgproxy.source_base_url'));
        $this->assertSame('', $this->getParameter('threebrs_imgproxy.source_prefix'));
        $this->assertNull($this->getParameter('threebrs_imgproxy.key'));
        $this->assertNull($this->getParameter('threebrs_imgproxy.salt'));
    }

    public function testServicesAreSharedInstances(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        // Get the same service twice
        $urlBuilder1 = $this->getService(ImgproxyUrlBuilderInterface::class);
        $urlBuilder2 = $this->getService(ImgproxyUrlBuilderInterface::class);

        // Should be the same instance (shared service)
        $this->assertSame($urlBuilder1, $urlBuilder2);

        $converter1 = $this->getService(FilterConfigConverterInterface::class);
        $converter2 = $this->getService(FilterConfigConverterInterface::class);

        $this->assertSame($converter1, $converter2);
    }
}
