<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Imagine;

/**
 * Generates imgproxy URLs with optional signing
 */
class ImgproxyUrlBuilder implements ImgproxyUrlBuilderInterface
{
    private string $baseUrl;

    private ?string $key;

    private ?string $salt;

    private string $sourceBaseUrl;

    private string $sourcePrefix;

    public function __construct(
        string $baseUrl,
        string $sourceBaseUrl,
        string $sourcePrefix = '',
        ?string $key = null,
        ?string $salt = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->sourceBaseUrl = rtrim($sourceBaseUrl, '/');
        $this->sourcePrefix = trim($sourcePrefix, '/');
        $this->key = $key;
        $this->salt = $salt;
    }

    /**
     * Build imgproxy URL
     *
     * @param string $sourcePath Original image path
     * @param array<string, mixed> $options Imgproxy processing options
     *
     * @return string Complete imgproxy URL
     */
    public function build(string $sourcePath, array $options = []): string
    {
        // Build the source URL
        $sourceUrl = $this->buildSourceUrl($sourcePath);

        // Build processing options string
        $processingOptions = $this->buildProcessingOptions($options);

        // Build the path without signature
        // Imgproxy requires base64 encoded source URL
        $encodedSourceUrl = rtrim(strtr(base64_encode($sourceUrl), '+/', '-_'), '=');
        $path = sprintf('/%s/%s', $processingOptions, $encodedSourceUrl);

        // Add signature if key and salt are provided
        if ($this->key && $this->salt) {
            $signature = $this->generateSignature($path);

            return sprintf('%s/%s%s', $this->baseUrl, $signature, $path);
        }

        // For unsigned requests
        return sprintf('%s/insecure%s', $this->baseUrl, $path);
    }

    /**
     * Build the source URL for the image
     */
    private function buildSourceUrl(string $path): string
    {
        // Remove leading slash
        $path = ltrim($path, '/');

        // If path already contains protocol, use as is
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }

        // Build full S3/CDN URL with prefix
        // Example: https://bucket.endpoint.com/stage/media/image/01/23/product.jpg
        $parts = array_filter([
            $this->sourceBaseUrl,
            $this->sourcePrefix,
            $path,
        ]);

        return implode('/', $parts);
    }

    /**
     * Build processing options string for imgproxy
     *
     * @param array<string, mixed> $options
     */
    private function buildProcessingOptions(array $options): string
    {
        $parts = [];

        // Resize type and dimensions
        if (isset($options['resize'])) {
            $parts[] = sprintf('resize:%s', $options['resize']);
        }

        if (isset($options['width']) || isset($options['height'])) {
            $width = $options['width'] ?? 0;
            $height = $options['height'] ?? 0;
            $parts[] = sprintf(
                'size:%d:%d:%d:%d',
                $width,
                $height,
                $options['enlarge'] ?? 0,
                $options['extend'] ?? 0,
            );
        }

        // Format
        if (isset($options['format'])) {
            $parts[] = sprintf('format:%s', $options['format']);
        }

        // Quality
        if (isset($options['quality'])) {
            $parts[] = sprintf('quality:%d', $options['quality']);
        }

        // Background color
        if (isset($options['background'])) {
            $parts[] = sprintf('background:%s', ltrim($options['background'], '#'));
        }

        // Gravity (for positioning)
        if (isset($options['gravity'])) {
            $parts[] = sprintf('gravity:%s', $options['gravity']);
        }

        // Blur
        if (isset($options['blur'])) {
            $parts[] = sprintf('blur:%s', $options['blur']);
        }

        // Sharpen
        if (isset($options['sharpen'])) {
            $parts[] = sprintf('sharpen:%s', $options['sharpen']);
        }

        // Auto rotate
        if (isset($options['auto_rotate']) && $options['auto_rotate']) {
            $parts[] = 'auto_rotate:1';
        }

        // Rotate
        if (isset($options['rotate'])) {
            $parts[] = sprintf('rotate:%d', $options['rotate']);
        }

        // Brightness
        if (isset($options['brightness'])) {
            $parts[] = sprintf('brightness:%d', $options['brightness']);
        }

        // Contrast
        if (isset($options['contrast'])) {
            $parts[] = sprintf('contrast:%d', $options['contrast']);
        }

        // Saturation (used for grayscale when set to 0)
        if (isset($options['saturation'])) {
            $parts[] = sprintf('saturation:%d', $options['saturation']);
        }

        // Watermark
        if (isset($options['watermark_url'])) {
            $watermarkUrl = rtrim(strtr(base64_encode($options['watermark_url']), '+/', '-_'), '=');
            $opacity = $options['watermark_opacity'] ?? 1;
            $position = $options['watermark_position'] ?? 'ce';
            $scale = $options['watermark_scale'] ?? 1;

            $parts[] = sprintf(
                'watermark:%s:%s:0:0:%s',
                $opacity,
                $position,
                $scale,
            );
        }

        // Strip _note keys (used for documentation only)
        unset($options['_note']);

        return implode('/', $parts);
    }

    /**
     * Generate HMAC signature for the path
     */
    private function generateSignature(string $path): string
    {
        $binaryKey = pack('H*', $this->key);
        $binarySalt = pack('H*', $this->salt);

        $signature = hash_hmac('sha256', $binarySalt . $path, $binaryKey, true);

        return rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }
}
