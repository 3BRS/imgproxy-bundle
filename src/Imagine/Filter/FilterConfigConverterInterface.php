<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Imagine\Filter;

/**
 * Interface for converting Liip Imagine filter configuration to imgproxy parameters
 */
interface FilterConfigConverterInterface
{
    /**
     * Convert Liip Imagine filter config to imgproxy options
     *
     * @param string $filter Filter name
     *
     * @return array<string, mixed> imgproxy options
     */
    public function convert(string $filter): array;
}
