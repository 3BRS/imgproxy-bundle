<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit\Imagine\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use PHPUnit\Framework\TestCase;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverter;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverterInterface;

final class FilterConfigConverterTest extends TestCase
{
    private FilterConfiguration $filterConfig;
    private FilterConfigConverter $converter;

    protected function setUp(): void
    {
        $this->filterConfig = $this->createMock(FilterConfiguration::class);
        $this->converter = new FilterConfigConverter($this->filterConfig);
    }

    public function testImplementsFilterConfigConverterInterface(): void
    {
        $this->assertInstanceOf(FilterConfigConverterInterface::class, $this->converter);
    }

    public function testConvertWithEmptyConfig(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('empty_filter')
            ->willReturn([]);

        $result = $this->converter->convert('empty_filter');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testConvertWithQuality(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('quality_filter')
            ->willReturn(['quality' => 85]);

        $result = $this->converter->convert('quality_filter');

        $this->assertArrayHasKey('quality', $result);
        $this->assertSame(85, $result['quality']);
    }

    public function testConvertWithFormat(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('format_filter')
            ->willReturn(['format' => 'webp']);

        $result = $this->converter->convert('format_filter');

        $this->assertArrayHasKey('format', $result);
        $this->assertSame('webp', $result['format']);
    }

    public function testConvertThumbnailOutbound(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200],
                        'mode' => 'outbound',
                    ],
                ],
            ]);

        $result = $this->converter->convert('thumbnail');

        $this->assertSame(300, $result['width']);
        $this->assertSame(200, $result['height']);
        $this->assertSame('fill', $result['resize']);
        $this->assertSame(1, $result['enlarge']);
    }

    public function testConvertThumbnailInset(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200],
                        'mode' => 'inset',
                    ],
                ],
            ]);

        $result = $this->converter->convert('thumbnail');

        $this->assertSame(300, $result['width']);
        $this->assertSame(200, $result['height']);
        $this->assertSame('fit', $result['resize']);
        $this->assertSame(0, $result['enlarge']);
    }

    public function testConvertThumbnailWithAllowUpscale(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200],
                        'mode' => 'inset',
                        'allow_upscale' => true,
                    ],
                ],
            ]);

        $result = $this->converter->convert('thumbnail');

        $this->assertSame(1, $result['enlarge']);
    }

    public function testConvertResize(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('resize')
            ->willReturn([
                'filters' => [
                    'resize' => [
                        'size' => [800, 600],
                    ],
                ],
            ]);

        $result = $this->converter->convert('resize');

        $this->assertSame(800, $result['width']);
        $this->assertSame(600, $result['height']);
        $this->assertSame('force', $result['resize']);
    }

    public function testConvertScale(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('scale')
            ->willReturn([
                'filters' => [
                    'scale' => [
                        'dim' => [400, 300],
                    ],
                ],
            ]);

        $result = $this->converter->convert('scale');

        $this->assertSame(400, $result['width']);
        $this->assertSame(300, $result['height']);
        $this->assertSame('fit', $result['resize']);
    }

    public function testConvertCrop(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('crop')
            ->willReturn([
                'filters' => [
                    'crop' => [
                        'size' => [200, 200],
                        'start' => [100, 100],
                    ],
                ],
            ]);

        $result = $this->converter->convert('crop');

        $this->assertSame(200, $result['width']);
        $this->assertSame(200, $result['height']);
        $this->assertSame('fill', $result['resize']);
        $this->assertSame('ce', $result['gravity']);
    }

    public function testConvertDownscale(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('downscale')
            ->willReturn([
                'filters' => [
                    'downscale' => [
                        'max' => [1920, 1080],
                    ],
                ],
            ]);

        $result = $this->converter->convert('downscale');

        $this->assertSame(1920, $result['width']);
        $this->assertSame(1080, $result['height']);
        $this->assertSame('fit', $result['resize']);
        $this->assertSame(0, $result['enlarge']);
    }

    public function testConvertUpscale(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('upscale')
            ->willReturn([
                'filters' => [
                    'upscale' => [
                        'min' => [800, 600],
                    ],
                ],
            ]);

        $result = $this->converter->convert('upscale');

        $this->assertSame(800, $result['width']);
        $this->assertSame(600, $result['height']);
        $this->assertSame('fit', $result['resize']);
        $this->assertSame(1, $result['enlarge']);
    }

    public function testConvertBackgroundColor(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('background')
            ->willReturn([
                'filters' => [
                    'background' => [
                        'color' => '#ffffff',
                    ],
                ],
            ]);

        $result = $this->converter->convert('background');

        $this->assertArrayHasKey('background', $result);
        $this->assertSame('#ffffff', $result['background']);
    }

    public function testConvertBackgroundWithSize(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('background')
            ->willReturn([
                'filters' => [
                    'background' => [
                        'color' => '#ffffff',
                        'size' => [1000, 800],
                    ],
                ],
            ]);

        $result = $this->converter->convert('background');

        $this->assertSame('#ffffff', $result['background']);
        $this->assertSame(1000, $result['width']);
        $this->assertSame(800, $result['height']);
        $this->assertSame(1, $result['extend']);
    }

    public function testConvertBackgroundDoesNotOverrideExistingResize(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('complex')
            ->willReturn([
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200],
                        'mode' => 'outbound',
                    ],
                    'background' => [
                        'color' => '#ffffff',
                        'size' => [400, 300],
                    ],
                ],
            ]);

        $result = $this->converter->convert('complex');

        // thumbnail sets resize to 'fill', background should not override it
        $this->assertSame('fill', $result['resize']);
        // thumbnail dimensions should remain (not overridden by background)
        $this->assertSame(300, $result['width']);
        $this->assertSame(200, $result['height']);
        // but background color should be applied
        $this->assertSame('#ffffff', $result['background']);
    }

    public function testConvertRotate(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('rotate')
            ->willReturn([
                'filters' => [
                    'rotate' => [
                        'angle' => 90,
                    ],
                ],
            ]);

        $result = $this->converter->convert('rotate');

        $this->assertArrayHasKey('rotate', $result);
        $this->assertSame(90, $result['rotate']);
    }

    public function testConvertAutoRotate(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('auto_rotate')
            ->willReturn([
                'filters' => [
                    'auto_rotate' => [],
                ],
            ]);

        $result = $this->converter->convert('auto_rotate');

        $this->assertArrayHasKey('auto_rotate', $result);
        $this->assertTrue($result['auto_rotate']);
    }

    public function testConvertFlipHorizontal(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('flip')
            ->willReturn([
                'filters' => [
                    'flip' => [
                        'axis' => 'x',
                    ],
                ],
            ]);

        $result = $this->converter->convert('flip');

        $this->assertArrayHasKey('_note', $result);
        $this->assertSame('horizontal_flip_not_supported', $result['_note']);
    }

    public function testConvertFlipVertical(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('flip')
            ->willReturn([
                'filters' => [
                    'flip' => [
                        'axis' => 'y',
                    ],
                ],
            ]);

        $result = $this->converter->convert('flip');

        $this->assertArrayHasKey('_note', $result);
        $this->assertSame('vertical_flip_not_supported', $result['_note']);
    }

    public function testConvertFlipNormalizesHorizontalAxis(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('flip')
            ->willReturn([
                'filters' => [
                    'flip' => [
                        'axis' => 'horizontal',
                    ],
                ],
            ]);

        $result = $this->converter->convert('flip');

        $this->assertSame('horizontal_flip_not_supported', $result['_note']);
    }

    public function testConvertFlipNormalizesVerticalAxis(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('flip')
            ->willReturn([
                'filters' => [
                    'flip' => [
                        'axis' => 'vertical',
                    ],
                ],
            ]);

        $result = $this->converter->convert('flip');

        $this->assertSame('vertical_flip_not_supported', $result['_note']);
    }

    public function testConvertGrayscale(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('grayscale')
            ->willReturn([
                'filters' => [
                    'grayscale' => [],
                ],
            ]);

        $result = $this->converter->convert('grayscale');

        $this->assertArrayHasKey('saturation', $result);
        $this->assertSame(0, $result['saturation']);
    }

    public function testConvertWatermark(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('watermark')
            ->willReturn([
                'filters' => [
                    'watermark' => [
                        'image' => 'watermark.png',
                        'opacity' => 0.5,
                        'position' => 'bottomright',
                        'size' => 0.3,
                    ],
                ],
            ]);

        $result = $this->converter->convert('watermark');

        $this->assertSame('watermark.png', $result['watermark_url']);
        $this->assertEqualsWithDelta(0.5, $result['watermark_opacity'], PHP_FLOAT_EPSILON);
        $this->assertSame('se', $result['watermark_position']);
        $this->assertEqualsWithDelta(0.3, $result['watermark_scale'], PHP_FLOAT_EPSILON);
    }

    public function testConvertWatermarkWithDefaultValues(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('watermark')
            ->willReturn([
                'filters' => [
                    'watermark' => [
                        'image' => 'watermark.png',
                    ],
                ],
            ]);

        $result = $this->converter->convert('watermark');

        $this->assertSame('watermark.png', $result['watermark_url']);
        $this->assertEqualsWithDelta(1.0, $result['watermark_opacity'], PHP_FLOAT_EPSILON);
        $this->assertSame('ce', $result['watermark_position']);
    }

    /**
     * @dataProvider watermarkPositionProvider
     */
    public function testConvertWatermarkPosition(string $liipPosition, string $expectedImgproxyPosition): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('watermark')
            ->willReturn([
                'filters' => [
                    'watermark' => [
                        'image' => 'watermark.png',
                        'position' => $liipPosition,
                    ],
                ],
            ]);

        $result = $this->converter->convert('watermark');

        $this->assertSame($expectedImgproxyPosition, $result['watermark_position']);
    }

    public static function watermarkPositionProvider(): \Iterator
    {
        yield ['topleft', 'no'];
        yield ['top', 'no'];
        yield ['topright', 'ne'];
        yield ['left', 'we'];
        yield ['center', 'ce'];
        yield ['right', 'ea'];
        yield ['bottomleft', 'sw'];
        yield ['bottom', 'so'];
        yield ['bottomright', 'se'];
    }

    public function testConvertRelativeResize(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('relative_resize')
            ->willReturn([
                'filters' => [
                    'relative_resize' => [
                        'scale' => 0.5,
                    ],
                ],
            ]);

        $result = $this->converter->convert('relative_resize');

        $this->assertArrayHasKey('resize', $result);
        $this->assertSame('fit', $result['resize']);
    }

    public function testConvertIgnoresStripFilter(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('strip')
            ->willReturn([
                'filters' => [
                    'strip' => [],
                ],
            ]);

        $result = $this->converter->convert('strip');

        // strip filter should be ignored (no-op)
        $this->assertEmpty($result);
    }

    public function testConvertIgnoresInterlaceFilter(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('interlace')
            ->willReturn([
                'filters' => [
                    'interlace' => [],
                ],
            ]);

        $result = $this->converter->convert('interlace');

        // interlace filter should be ignored (no-op)
        $this->assertEmpty($result);
    }

    public function testConvertIgnoresPasteFilter(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('paste')
            ->willReturn([
                'filters' => [
                    'paste' => [],
                ],
            ]);

        $result = $this->converter->convert('paste');

        // paste filter should be ignored (no-op)
        $this->assertEmpty($result);
    }

    public function testConvertIgnoresResampleFilter(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('resample')
            ->willReturn([
                'filters' => [
                    'resample' => [],
                ],
            ]);

        $result = $this->converter->convert('resample');

        // resample filter should be ignored (no-op)
        $this->assertEmpty($result);
    }

    public function testConvertMultipleFilters(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('complex')
            ->willReturn([
                'quality' => 85,
                'format' => 'webp',
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200],
                        'mode' => 'outbound',
                    ],
                    'rotate' => [
                        'angle' => 90,
                    ],
                    'auto_rotate' => [],
                ],
            ]);

        $result = $this->converter->convert('complex');

        $this->assertSame(85, $result['quality']);
        $this->assertSame('webp', $result['format']);
        $this->assertSame(300, $result['width']);
        $this->assertSame(200, $result['height']);
        $this->assertSame('fill', $result['resize']);
        $this->assertSame(90, $result['rotate']);
        $this->assertTrue($result['auto_rotate']);
    }

    public function testConvertHandlesNonArrayFilterConfig(): void
    {
        $this->filterConfig
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn([
                'filters' => [
                    'strip' => null, // non-array config
                ],
            ]);

        $result = $this->converter->convert('test');

        // Should handle gracefully without errors
        $this->assertIsArray($result);
    }
}
