<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Imagine\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

/**
 * Converts Liip Imagine filter configuration to imgproxy parameters
 */
class FilterConfigConverter implements FilterConfigConverterInterface
{
    private FilterConfiguration $filterConfig;

    public function __construct(FilterConfiguration $filterConfig)
    {
        $this->filterConfig = $filterConfig;
    }

    /**
     * Convert Liip Imagine filter config to imgproxy options
     *
     * @param string $filter Filter name
     *
     * @return array<string, mixed> imgproxy options
     */
    public function convert(string $filter): array
    {
        $config = $this->filterConfig->get($filter);
        $options = [];

        // Handle quality setting (global or in filters)
        if (isset($config['quality'])) {
            $options['quality'] = $config['quality'];
        }

        // Handle format conversion
        if (isset($config['format'])) {
            $options['format'] = $config['format'];
        }

        // Process filters
        if (isset($config['filters']) && is_array($config['filters'])) {
            $options = array_merge($options, $this->convertFilters($config['filters']));
        }

        return $options;
    }

    /**
     * Convert individual filters to imgproxy options
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function convertFilters(array $filters): array
    {
        $options = [];

        foreach ($filters as $filterName => $filterConfig) {
            if (!is_array($filterConfig)) {
                $filterConfig = [];
            }

            switch ($filterName) {
                case 'thumbnail':
                    $options = array_merge($options, $this->convertThumbnail($filterConfig));

                    break;
                case 'resize':
                    $options = array_merge($options, $this->convertResize($filterConfig));

                    break;
                case 'relative_resize':
                    $options = array_merge($options, $this->convertRelativeResize($filterConfig));

                    break;
                case 'scale':
                    $options = array_merge($options, $this->convertScale($filterConfig));

                    break;
                case 'downscale':
                    $options = array_merge($options, $this->convertDownscale($filterConfig));

                    break;
                case 'upscale':
                    $options = array_merge($options, $this->convertUpscale($filterConfig));

                    break;
                case 'crop':
                    $options = array_merge($options, $this->convertCrop($filterConfig));

                    break;
                case 'background':
                    $backgroundOptions = $this->convertBackground($filterConfig);
                    // Don't let background filter override resize mode or dimensions from previous filters
                    if (isset($options['resize'])) {
                        unset($backgroundOptions['resize']);
                    }
                    if (isset($options['width'])) {
                        unset($backgroundOptions['width']);
                    }
                    if (isset($options['height'])) {
                        unset($backgroundOptions['height']);
                    }
                    $options = array_merge($options, $backgroundOptions);

                    break;
                case 'rotate':
                    $options = array_merge($options, $this->convertRotate($filterConfig));

                    break;
                case 'auto_rotate':
                    $options['auto_rotate'] = true;

                    break;
                case 'flip':
                    $options = array_merge($options, $this->convertFlip($filterConfig));

                    break;
                case 'grayscale':
                    // imgproxy: use modulate with saturation 0
                    $options['saturation'] = 0;

                    break;
                case 'watermark':
                    $options = array_merge($options, $this->convertWatermark($filterConfig));

                    break;
                case 'strip':
                    // imgproxy strips metadata by default
                    break;
                case 'interlace':
                    // imgproxy enables progressive JPEG and interlaced PNG by default
                    break;
                case 'paste':
                    // Not supported by imgproxy - complex operation
                    // Would need fallback to Liip Imagine processing
                    break;
                case 'resample':
                    // DPI changes are not relevant for web images
                    break;
            }
        }

        return $options;
    }

    /**
     * Convert thumbnail filter (most common)
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertThumbnail(array $config): array
    {
        $options = [];

        if (isset($config['size'])) {
            [$width, $height] = $config['size'];
            $options['width'] = $width;
            $options['height'] = $height;
        }

        // Mode determines resize type
        $mode = $config['mode'] ?? 'outbound';

        switch ($mode) {
            case 'outbound':
                // Crop to exact dimensions
                $options['resize'] = 'fill';
                $options['enlarge'] = 1;

                break;
            case 'inset':
                // Fit within dimensions, may be smaller
                $options['resize'] = 'fit';
                $options['enlarge'] = 0;

                break;
        }

        // Allow upscaling
        if (isset($config['allow_upscale']) && $config['allow_upscale']) {
            $options['enlarge'] = 1;
        }

        return $options;
    }

    /**
     * Convert relative_resize filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertRelativeResize(array $config): array
    {
        // This is more complex - for now we'll handle basic scaling
        $options = [];

        if (isset($config['scale'])) {
            // imgproxy doesn't have direct scaling, we'd need to calculate actual dimensions
            // This would require knowing source image dimensions
            $options['resize'] = 'fit';
        }

        return $options;
    }

    /**
     * Convert background filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertBackground(array $config): array
    {
        $options = [];

        if (isset($config['color'])) {
            $options['background'] = $config['color'];
        }

        if (isset($config['size'])) {
            [$width, $height] = $config['size'];
            $options['width'] = $width;
            $options['height'] = $height;
            // Enable extend to add background padding around the resized image
            $options['extend'] = 1;
            // Don't set resize mode - let previous filters (like thumbnail) determine it
            // If no previous resize mode was set, imgproxy will default to 'fit'
        }

        return $options;
    }

    /**
     * Convert scale filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertScale(array $config): array
    {
        $options = [];

        if (isset($config['dim'])) {
            [$width, $height] = $config['dim'];
            $options['width'] = $width;
            $options['height'] = $height;
            $options['resize'] = 'fit';
        }

        return $options;
    }

    /**
     * Convert crop filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertCrop(array $config): array
    {
        $options = [];

        if (isset($config['size'])) {
            [$width, $height] = $config['size'];
            $options['width'] = $width;
            $options['height'] = $height;
            $options['resize'] = 'fill';
        }

        // Start position for crop
        if (isset($config['start'])) {
            // imgproxy uses gravity instead of exact coordinates
            $options['gravity'] = 'ce'; // center by default
        }

        return $options;
    }

    /**
     * Convert resize filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertResize(array $config): array
    {
        $options = [];

        if (isset($config['size'])) {
            [$width, $height] = $config['size'];
            $options['width'] = $width ?? 0;
            $options['height'] = $height ?? 0;
            $options['resize'] = 'force'; // Force exact dimensions
        }

        return $options;
    }

    /**
     * Convert downscale filter - only shrink, never enlarge
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertDownscale(array $config): array
    {
        $options = [];

        if (isset($config['max'])) {
            [$width, $height] = $config['max'];
            $options['width'] = $width ?? 0;
            $options['height'] = $height ?? 0;
            $options['resize'] = 'fit';
            $options['enlarge'] = 0; // Never enlarge
        }

        return $options;
    }

    /**
     * Convert upscale filter - scale up to minimum dimensions
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertUpscale(array $config): array
    {
        $options = [];

        if (isset($config['min'])) {
            [$width, $height] = $config['min'];
            $options['width'] = $width ?? 0;
            $options['height'] = $height ?? 0;
            $options['resize'] = 'fit';
            $options['enlarge'] = 1; // Allow enlarging
        }

        return $options;
    }

    /**
     * Convert rotate filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertRotate(array $config): array
    {
        $options = [];

        if (isset($config['angle'])) {
            $options['rotate'] = (int) $config['angle'];
        }

        return $options;
    }

    /**
     * Convert flip filter
     * Note: imgproxy doesn't have native flip, so we use rotate for some cases
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertFlip(array $config): array
    {
        $options = [];

        $axis = $config['axis'] ?? 'x';

        // Normalize axis
        if ($axis === 'horizontal') {
            $axis = 'x';
        } elseif ($axis === 'vertical') {
            $axis = 'y';
        }

        // imgproxy doesn't have direct flip support
        // We'll use a workaround: combine rotate and flip when imgproxy adds support
        // For now, we note this limitation
        if ($axis === 'x') {
            // Horizontal flip - not directly supported
            // Could be approximated with rotate:180 but that's not the same
            $options['_note'] = 'horizontal_flip_not_supported';
        } else {
            // Vertical flip - not directly supported
            $options['_note'] = 'vertical_flip_not_supported';
        }

        return $options;
    }

    /**
     * Convert watermark filter
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function convertWatermark(array $config): array
    {
        $options = [];

        if (isset($config['image'])) {
            // imgproxy watermark syntax: watermark:opacity:position:x_offset:y_offset:scale
            $opacity = $config['opacity'] ?? 1.0;
            $position = $this->convertWatermarkPosition($config['position'] ?? 'center');

            // imgproxy requires watermark to be a URL
            // This is complex as we need to convert file path to URL
            $options['watermark_url'] = $config['image'];
            $options['watermark_opacity'] = $opacity;
            $options['watermark_position'] = $position;

            if (isset($config['size'])) {
                $options['watermark_scale'] = $config['size'];
            }
        }

        return $options;
    }

    /**
     * Convert Liip watermark position to imgproxy gravity
     */
    private function convertWatermarkPosition(string $position): string
    {
        $positionMap = [
            'topleft' => 'no',
            'top' => 'no',
            'topright' => 'ne',
            'left' => 'we',
            'center' => 'ce',
            'right' => 'ea',
            'bottomleft' => 'sw',
            'bottom' => 'so',
            'bottomright' => 'se',
        ];

        return $positionMap[$position] ?? 'ce';
    }
}
