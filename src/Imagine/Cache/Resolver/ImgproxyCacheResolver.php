<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use ThreeBRS\ImgproxyBundle\Imagine\Filter\FilterConfigConverterInterface;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilderInterface;

/**
 * Cache resolver that generates imgproxy URLs instead of processing images locally
 */
class ImgproxyCacheResolver implements ResolverInterface
{
    private ImgproxyUrlBuilderInterface $urlBuilder;

    private FilterConfigConverterInterface $configConverter;

    public function __construct(
        ImgproxyUrlBuilderInterface $urlBuilder,
        FilterConfigConverterInterface $configConverter,
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configConverter = $configConverter;
    }

    public function isStored($path, $filter): bool
    {
        // imgproxy generates URLs on-the-fly, so images are always "stored"
        // This prevents Liip Imagine from trying to generate the image
        return true;
    }

    public function resolve($path, $filter): string
    {
        // Skip imgproxy for static assets (webpack encore builds, etc.)
        if ($this->isStaticAsset($path)) {
            // Return the path as-is (already a full URL from asset())
            return $path;
        }

        // Convert Liip Imagine filter config to imgproxy options
        $options = $this->configConverter->convert($filter);

        // Generate and return imgproxy URL
        return $this->urlBuilder->build($path, $options);
    }

    public function store(BinaryInterface $binary, $path, $filter): void
    {
        // No-op: imgproxy doesn't store images, it generates them on-the-fly
        // Images are stored in the original S3 bucket
    }

    public function remove(array $paths, array $filters): void
    {
        // No-op: nothing to remove since imgproxy doesn't cache locally
        // Cache is managed by imgproxy itself
    }

    /**
     * Check if path is a static asset that shouldn't be processed by imgproxy
     */
    private function isStaticAsset(string $path): bool
    {
        // Check if it's already a full URL containing /build/ (webpack encore assets)
        if (str_contains($path, '/build/')) {
            return true;
        }

        // Check if it's a full URL to CDN that contains /build/
        if (preg_match('#^https?://.*?/build/#', $path)) {
            return true;
        }

        // Check for other static asset patterns
        $staticPaths = [
            '/bundles/',
            '/assets/',
        ];

        foreach ($staticPaths as $staticPath) {
            if (str_contains($path, $staticPath)) {
                return true;
            }
        }

        return false;
    }
}
