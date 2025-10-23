<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Imagine;

/**
 * Interface for generating imgproxy URLs with optional signing
 */
interface ImgproxyUrlBuilderInterface
{
    /**
     * Build imgproxy URL
     *
     * @param string $sourcePath Original image path
     * @param array<string, mixed> $options Imgproxy processing options
     *
     * @return string Complete imgproxy URL
     */
    public function build(string $sourcePath, array $options = []): string;
}
