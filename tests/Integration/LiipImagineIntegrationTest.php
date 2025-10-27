<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Integration;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use ThreeBRS\ImgproxyBundle\Imagine\Cache\Resolver\ImgproxyCacheResolver;

/**
 * Tests integration with LiipImagineBundle
 */
final class LiipImagineIntegrationTest extends IntegrationTestCase
{
    public function testLiipImagineBundleIsLoaded(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        // Check that Liip services are available
        $this->assertTrue($this->hasService('liip_imagine.filter.configuration'));
        $this->assertTrue($this->hasService('liip_imagine.cache.manager'));
    }

    public function testFilterConfigurationServiceIsAccessible(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $filterConfig = $this->getService('liip_imagine.filter.configuration');

        $this->assertInstanceOf(FilterConfiguration::class, $filterConfig);

        // Test that we can access filter sets defined in kernel
        $thumbnailConfig = $filterConfig->get('thumbnail');
        $this->assertIsArray($thumbnailConfig);
        $this->assertArrayHasKey('filters', $thumbnailConfig);
        $this->assertArrayHasKey('thumbnail', $thumbnailConfig['filters']);

        $smallConfig = $filterConfig->get('small');
        $this->assertIsArray($smallConfig);
        $this->assertArrayHasKey('filters', $smallConfig);
    }

    public function testImgproxyCacheResolverCanBeUsedAsLiipResolver(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Verify it implements Liip's resolver interface
        $this->assertInstanceOf(ResolverInterface::class, $resolver);

        // Test resolve method (required by interface)
        $url = $resolver->resolve('test.jpg', 'thumbnail');
        $this->assertIsString($url);
        $this->assertStringContainsString('https://imgproxy.example.com', $url);

        // Test isStored method (required by interface)
        // Should return true to prevent Liip Imagine from trying to process images locally
        $this->assertTrue($resolver->isStored('test.jpg', 'thumbnail'));
    }

    public function testCacheManagerWithImgproxyResolver(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        // We can't directly test CacheManager with our resolver without more complex setup,
        // but we can verify the resolver is properly configured
        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Test that resolver works with different filters
        $filters = ['thumbnail', 'small'];

        foreach ($filters as $filter) {
            $url = $resolver->resolve('image.jpg', $filter);
            $this->assertIsString($url);
            $this->assertNotEmpty($url);
        }
    }

    public function testFilterConfigurationContainsExpectedFilters(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $filterConfig = $this->getService('liip_imagine.filter.configuration');

        // Verify 'thumbnail' filter configuration
        $thumbnail = $filterConfig->get('thumbnail');
        $this->assertSame(85, $thumbnail['quality']);
        $this->assertArrayHasKey('thumbnail', $thumbnail['filters']);
        $this->assertSame([300, 200], $thumbnail['filters']['thumbnail']['size']);
        $this->assertSame('outbound', $thumbnail['filters']['thumbnail']['mode']);

        // Verify 'small' filter configuration
        $small = $filterConfig->get('small');
        $this->assertSame(90, $small['quality']);
        $this->assertArrayHasKey('thumbnail', $small['filters']);
        $this->assertSame([150, 150], $small['filters']['thumbnail']['size']);
        $this->assertSame('inset', $small['filters']['thumbnail']['mode']);
    }

    public function testImgproxyConvertsLiipFiltersCorrectly(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $converter = $this->getService('threebrs_imgproxy.filter_converter');
        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Test that Liip's 'outbound' mode converts to imgproxy 'fill'
        $thumbnailOptions = $converter->convert('thumbnail');
        $this->assertSame('fill', $thumbnailOptions['resize']);
        $this->assertSame(1, $thumbnailOptions['enlarge']);

        // Test that Liip's 'inset' mode converts to imgproxy 'fit'
        $smallOptions = $converter->convert('small');
        $this->assertSame('fit', $smallOptions['resize']);
        $this->assertSame(0, $smallOptions['enlarge']);

        // Verify URLs are generated correctly
        $thumbnailUrl = $resolver->resolve('test.jpg', 'thumbnail');
        $this->assertStringContainsString('resize:fill', $thumbnailUrl);
        $this->assertStringContainsString('size:300:200:1:0', $thumbnailUrl); // includes enlarge:1

        $smallUrl = $resolver->resolve('test.jpg', 'small');
        $this->assertStringContainsString('resize:fit', $smallUrl);
        $this->assertStringContainsString('size:150:150:0:0', $smallUrl); // includes enlarge:0
    }

    public function testResolverHandlesMissingFilter(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Liip's FilterConfiguration throws exception for non-existent filters
        $this->expectException(\RuntimeException::class);
        $resolver->resolve('test.jpg', 'non_existent_filter');
    }

    public function testResolverUsesFilterQualitySettings(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // 'thumbnail' has quality 85
        $thumbnailUrl = $resolver->resolve('test.jpg', 'thumbnail');
        $this->assertStringContainsString('quality:85', $thumbnailUrl);

        // 'small' has quality 90
        $smallUrl = $resolver->resolve('test.jpg', 'small');
        $this->assertStringContainsString('quality:90', $smallUrl);
    }
}
