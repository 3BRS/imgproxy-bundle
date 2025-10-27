<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use ThreeBRS\ImgproxyBundle\ThreeBRSImgproxyBundle;

final class ThreeBRSImgproxyBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new ThreeBRSImgproxyBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new ThreeBRSImgproxyBundle();

        $this->assertNotNull($bundle);
    }
}
