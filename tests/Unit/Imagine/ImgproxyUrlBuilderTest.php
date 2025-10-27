<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit\Imagine;

use PHPUnit\Framework\TestCase;
use ThreeBRS\ImgproxyBundle\Imagine\ImgproxyUrlBuilder;

final class ImgproxyUrlBuilderTest extends TestCase
{
    private const BASE_URL = 'https://imgproxy.example.com';
    private const SOURCE_BASE_URL = 'https://cdn.example.com';
    private const SOURCE_PREFIX = 'media/images';

    public function testBuildUnsignedUrlWithoutOptions(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg');

        $this->assertStringStartsWith(self::BASE_URL . '/insecure/', $url);
        $this->assertStringContainsString(base64_encode(self::SOURCE_BASE_URL . '/' . self::SOURCE_PREFIX . '/product.jpg'), $url);
    }

    public function testBuildUnsignedUrlWithResizeOptions(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'resize' => 'fill',
            'width' => 300,
            'height' => 200,
        ]);

        $this->assertStringStartsWith(self::BASE_URL . '/insecure/', $url);
        $this->assertStringContainsString('resize:fill', $url);
        $this->assertStringContainsString('size:300:200', $url);
    }

    public function testBuildSignedUrl(): void
    {
        $key = bin2hex(random_bytes(16));
        $salt = bin2hex(random_bytes(16));

        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX,
            $key,
            $salt
        );

        $url = $builder->build('product.jpg');

        $this->assertStringStartsWith(self::BASE_URL . '/', $url);
        $this->assertStringNotContainsString('/insecure/', $url);
        // Signed URL should have signature before the path
        $this->assertMatchesRegularExpression('#^' . preg_quote(self::BASE_URL, '#') . '/[A-Za-z0-9_-]+/#', $url);
    }

    public function testBuildWithQualityOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'quality' => 85,
        ]);

        $this->assertStringContainsString('quality:85', $url);
    }

    public function testBuildWithFormatOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'format' => 'webp',
        ]);

        $this->assertStringContainsString('format:webp', $url);
    }

    public function testBuildWithBackgroundOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'background' => '#ffffff',
        ]);

        $this->assertStringContainsString('background:ffffff', $url);
    }

    public function testBuildWithRotateOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'rotate' => 90,
        ]);

        $this->assertStringContainsString('rotate:90', $url);
    }

    public function testBuildWithAutoRotateOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'auto_rotate' => true,
        ]);

        $this->assertStringContainsString('auto_rotate:1', $url);
    }

    public function testBuildWithGravityOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'gravity' => 'ce',
        ]);

        $this->assertStringContainsString('gravity:ce', $url);
    }

    public function testBuildWithEnlargeOption(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'width' => 300,
            'height' => 200,
            'enlarge' => 1,
        ]);

        $this->assertStringContainsString('size:300:200:1', $url);
    }

    public function testBuildWithAbsoluteSourceUrl(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $absoluteUrl = 'https://other-cdn.example.com/image.jpg';
        $url = $builder->build($absoluteUrl);

        $encodedUrl = rtrim(strtr(base64_encode($absoluteUrl), '+/', '-_'), '=');
        $this->assertStringContainsString($encodedUrl, $url);
    }

    public function testBuildWithEmptyPrefix(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            '' // empty prefix
        );

        $url = $builder->build('product.jpg');

        $expectedSourceUrl = self::SOURCE_BASE_URL . '/product.jpg';
        $encodedUrl = rtrim(strtr(base64_encode($expectedSourceUrl), '+/', '-_'), '=');
        $this->assertStringContainsString($encodedUrl, $url);
    }

    public function testBuildTrimsSlashesFromUrls(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL . '/',
            self::SOURCE_BASE_URL . '/',
            '/' . self::SOURCE_PREFIX . '/'
        );

        $url = $builder->build('/product.jpg');

        // Builder should handle trailing/leading slashes gracefully
        $this->assertStringStartsWith(self::BASE_URL . '/insecure/', $url);

        // Source URL should be properly encoded
        $expectedSourceUrl = self::SOURCE_BASE_URL . '/' . self::SOURCE_PREFIX . '/product.jpg';
        $encodedUrl = rtrim(strtr(base64_encode($expectedSourceUrl), '+/', '-_'), '=');
        $this->assertStringContainsString($encodedUrl, $url);
    }

    public function testBuildWithMultipleOptions(): void
    {
        $builder = new ImgproxyUrlBuilder(
            self::BASE_URL,
            self::SOURCE_BASE_URL,
            self::SOURCE_PREFIX
        );

        $url = $builder->build('product.jpg', [
            'resize' => 'fill',
            'width' => 300,
            'height' => 200,
            'quality' => 85,
            'format' => 'webp',
            'auto_rotate' => true,
        ]);

        $this->assertStringContainsString('resize:fill', $url);
        $this->assertStringContainsString('size:300:200', $url);
        $this->assertStringContainsString('quality:85', $url);
        $this->assertStringContainsString('format:webp', $url);
        $this->assertStringContainsString('auto_rotate:1', $url);
    }
}
