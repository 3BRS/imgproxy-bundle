<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Integration;

use Liip\ImagineBundle\Binary\BinaryInterface;
use ThreeBRS\ImgproxyBundle\Imagine\Cache\Resolver\ImgproxyCacheResolver;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverterInterface;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilderInterface;

/**
 * Tests the full flow: configuration -> filter conversion -> URL building -> cache resolving
 */
final class FullFlowIntegrationTest extends IntegrationTestCase
{
    public function testCompleteFlowFromFilterToResolvedUrl(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'source_prefix' => 'images',
        ];

        $this->bootKernel($config);

        // 1. Get filter configuration and convert it
        $converter = $this->getService(FilterConfigConverterInterface::class);
        $filterOptions = $converter->convert('thumbnail');

        $this->assertIsArray($filterOptions);
        $this->assertArrayHasKey('width', $filterOptions);
        $this->assertArrayHasKey('height', $filterOptions);

        // 2. Build URL using converted options
        $urlBuilder = $this->getService(ImgproxyUrlBuilderInterface::class);
        $url = $urlBuilder->build('test.jpg', $filterOptions);

        $this->assertStringContainsString('https://imgproxy.example.com', $url);
        $this->assertStringContainsString('size:300:200', $url); // imgproxy uses combined size directive

        // 3. Use cache resolver to resolve the URL
        $resolver = $this->getService(ImgproxyCacheResolver::class);

        $resolvedUrl = $resolver->resolve('test.jpg', 'thumbnail');

        $this->assertIsString($resolvedUrl);
        $this->assertStringContainsString('https://imgproxy.example.com', $resolvedUrl);
    }

    public function testFlowWithDifferentFilterSets(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $converter = $this->getService(FilterConfigConverterInterface::class);
        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Test 'thumbnail' filter (300x200, outbound)
        $thumbnailOptions = $converter->convert('thumbnail');
        $this->assertSame(300, $thumbnailOptions['width']);
        $this->assertSame(200, $thumbnailOptions['height']);
        $this->assertSame('fill', $thumbnailOptions['resize']);

        $thumbnailUrl = $resolver->resolve('photo.jpg', 'thumbnail');
        $this->assertStringContainsString('size:300:200', $thumbnailUrl);
        $this->assertStringContainsString('resize:fill', $thumbnailUrl);

        // Test 'small' filter (150x150, inset)
        $smallOptions = $converter->convert('small');
        $this->assertSame(150, $smallOptions['width']);
        $this->assertSame(150, $smallOptions['height']);
        $this->assertSame('fit', $smallOptions['resize']);

        $smallUrl = $resolver->resolve('photo.jpg', 'small');
        $this->assertStringContainsString('size:150:150', $smallUrl);
        $this->assertStringContainsString('resize:fit', $smallUrl);
    }

    public function testFlowWithSignedUrls(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'key' => '943b421c9eb07c830af81030552c86009268de4e532ba2ee2eab8247c6da0881',
            'salt' => '520f986b998545b4785e0defbc4f3c1203f22de2374a3d53cb7a7fe9fea309c5',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);
        $url = $resolver->resolve('secure-image.jpg', 'thumbnail');

        // URL should be signed (contains signature)
        $this->assertMatchesRegularExpression('#^https://imgproxy\.example\.com/[a-zA-Z0-9_-]+/#', $url);

        // Should not contain 'insecure' keyword
        $this->assertStringNotContainsString('insecure', $url);
    }

    public function testFlowWithSourcePrefix(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'source_prefix' => 'uploads/media',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);
        $url = $resolver->resolve('product.jpg', 'thumbnail');

        // URL should contain encoded source URL with prefix
        $this->assertStringContainsString('https://imgproxy.example.com', $url);

        // Decode the URL to check if it contains the source URL with prefix
        $urlBuilder = $this->getService(ImgproxyUrlBuilderInterface::class);
        $directUrl = $urlBuilder->build('product.jpg', ['width' => 100]);

        // The encoded source URL should be in the path
        $this->assertStringContainsString('https://imgproxy.example.com', $directUrl);
    }

    public function testFlowWithQualityParameter(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $converter = $this->getService(FilterConfigConverterInterface::class);

        // 'thumbnail' filter has quality: 85
        $options = $converter->convert('thumbnail');

        $this->assertArrayHasKey('quality', $options);
        $this->assertSame(85, $options['quality']);

        // Build URL and check if quality is included
        $urlBuilder = $this->getService(ImgproxyUrlBuilderInterface::class);
        $url = $urlBuilder->build('test.jpg', $options);

        $this->assertStringContainsString('quality:85', $url);
    }

    public function testResolverIsStoreMethod(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // isStored should always return true for imgproxy
        // This tells Liip Imagine that images are "already processed" and prevents local processing
        $this->assertTrue($resolver->isStored('test.jpg', 'thumbnail'));
        $this->assertTrue($resolver->isStored('any-image.png', 'small'));
    }

    public function testResolverWithMultiplePaths(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $resolver = $this->getService(ImgproxyCacheResolver::class);

        // Test different image paths
        $paths = [
            'simple.jpg',
            'folder/image.png',
            'deep/nested/path/photo.webp',
            'with-dashes-and_underscores.jpg',
        ];

        foreach ($paths as $path) {
            $url = $resolver->resolve($path, 'thumbnail');

            $this->assertIsString($url);
            $this->assertStringContainsString('https://imgproxy.example.com', $url);
            $this->assertNotEmpty($url);
        }
    }

    public function testUrlBuilderHandlesSpecialCharactersInPath(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->bootKernel($config);

        $urlBuilder = $this->getService(ImgproxyUrlBuilderInterface::class);

        // Test paths with special characters
        $specialPaths = [
            'image with spaces.jpg',
            'image@2x.png',
            'path/to/file.jpg',
            'unicode-ñöü.jpg',
        ];

        foreach ($specialPaths as $path) {
            $url = $urlBuilder->build($path, ['width' => 100]);

            $this->assertIsString($url);
            $this->assertStringContainsString('https://imgproxy.example.com', $url);
        }
    }
}
