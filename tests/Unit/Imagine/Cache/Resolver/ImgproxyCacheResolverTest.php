<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;
use ThreeBRS\ImgproxyBundle\Imagine\Cache\Resolver\ImgproxyCacheResolver;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverterInterface;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilderInterface;

final class ImgproxyCacheResolverTest extends TestCase
{
    private ImgproxyUrlBuilderInterface $urlBuilder;
    private FilterConfigConverterInterface $configConverter;
    private ImgproxyCacheResolver $resolver;

    protected function setUp(): void
    {
        $this->urlBuilder = $this->createMock(ImgproxyUrlBuilderInterface::class);
        $this->configConverter = $this->createMock(FilterConfigConverterInterface::class);
        $this->resolver = new ImgproxyCacheResolver($this->urlBuilder, $this->configConverter);
    }

    public function testImplementsResolverInterface(): void
    {
        $this->assertInstanceOf(ResolverInterface::class, $this->resolver);
    }

    public function testIsStoredAlwaysReturnsTrue(): void
    {
        $this->assertTrue($this->resolver->isStored('path/to/image.jpg', 'thumbnail'));
        $this->assertTrue($this->resolver->isStored('another/path.png', 'large'));
    }

    public function testResolveBuildsImgproxyUrl(): void
    {
        $path = 'path/to/image.jpg';
        $filter = 'thumbnail';
        $options = ['width' => 300, 'height' => 200];
        $expectedUrl = 'https://imgproxy.example.com/insecure/resize:fill/size:300:200/encoded_url';

        $this->configConverter
            ->expects($this->once())
            ->method('convert')
            ->with($filter)
            ->willReturn($options);

        $this->urlBuilder
            ->expects($this->once())
            ->method('build')
            ->with($path, $options)
            ->willReturn($expectedUrl);

        $result = $this->resolver->resolve($path, $filter);

        $this->assertSame($expectedUrl, $result);
    }

    public function testResolveReturnsPathAsIsForStaticAssets(): void
    {
        $path = 'https://cdn.example.com/build/app.12345.css';
        $filter = 'thumbnail';

        $this->configConverter
            ->expects($this->never())
            ->method('convert');

        $this->urlBuilder
            ->expects($this->never())
            ->method('build');

        $result = $this->resolver->resolve($path, $filter);

        $this->assertSame($path, $result);
    }

    /**
     * @dataProvider staticAssetPathsProvider
     */
    public function testResolveSkipsStaticAssets(string $path): void
    {
        $filter = 'thumbnail';

        $this->configConverter
            ->expects($this->never())
            ->method('convert');

        $this->urlBuilder
            ->expects($this->never())
            ->method('build');

        $result = $this->resolver->resolve($path, $filter);

        $this->assertSame($path, $result);
    }

    public static function staticAssetPathsProvider(): \Iterator
    {
        yield 'webpack encore build' => ['https://cdn.example.com/build/app.12345.css'];
        yield 'bundles path' => ['/bundles/framework/css/structure.css'];
        yield 'assets path' => ['/assets/images/logo.png'];
        yield 'relative build path' => ['/build/images/logo.png'];
        yield 'cdn with build' => ['https://cdn.example.com/build/js/app.js'];
    }

    public function testStoreDoesNothing(): void
    {
        $binary = $this->createMock(BinaryInterface::class);

        // Should not throw any exception
        $this->resolver->store($binary, 'path/to/image.jpg', 'thumbnail');

        // If we get here, the method executed successfully
        $this->assertTrue(true);
    }

    public function testRemoveDoesNothing(): void
    {
        $paths = ['path/to/image1.jpg', 'path/to/image2.jpg'];
        $filters = ['thumbnail', 'large'];

        // Should not throw any exception
        $this->resolver->remove($paths, $filters);

        // If we get here, the method executed successfully
        $this->assertTrue(true);
    }

    public function testResolveWithEmptyOptions(): void
    {
        $path = 'path/to/image.jpg';
        $filter = 'original';
        $options = [];
        $expectedUrl = 'https://imgproxy.example.com/insecure/encoded_url';

        $this->configConverter
            ->expects($this->once())
            ->method('convert')
            ->with($filter)
            ->willReturn($options);

        $this->urlBuilder
            ->expects($this->once())
            ->method('build')
            ->with($path, $options)
            ->willReturn($expectedUrl);

        $result = $this->resolver->resolve($path, $filter);

        $this->assertSame($expectedUrl, $result);
    }

    public function testResolveWithComplexOptions(): void
    {
        $path = 'path/to/image.jpg';
        $filter = 'complex_filter';
        $options = [
            'width' => 800,
            'height' => 600,
            'quality' => 85,
            'format' => 'webp',
            'resize' => 'fill',
        ];
        $expectedUrl = 'https://imgproxy.example.com/signed/complex_url';

        $this->configConverter
            ->expects($this->once())
            ->method('convert')
            ->with($filter)
            ->willReturn($options);

        $this->urlBuilder
            ->expects($this->once())
            ->method('build')
            ->with($path, $options)
            ->willReturn($expectedUrl);

        $result = $this->resolver->resolve($path, $filter);

        $this->assertSame($expectedUrl, $result);
    }
}
