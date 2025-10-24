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

        self::assertStringStartsWith(self::BASE_URL . '/insecure/', $url);
        self::assertStringContainsString(base64_encode(self::SOURCE_BASE_URL . '/' . self::SOURCE_PREFIX . '/product.jpg'), $url);
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

        self::assertStringStartsWith(self::BASE_URL . '/insecure/', $url);
        self::assertStringContainsString('resize:fill', $url);
        self::assertStringContainsString('size:300:200', $url);
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

        self::assertStringStartsWith(self::BASE_URL . '/', $url);
        self::assertStringNotContainsString('/insecure/', $url);
        // Signed URL should have signature before the path
        self::assertMatchesRegularExpression('#^' . preg_quote(self::BASE_URL, '#') . '/[A-Za-z0-9_-]+/#', $url);
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

        self::assertStringContainsString('quality:85', $url);
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

        self::assertStringContainsString('format:webp', $url);
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

        self::assertStringContainsString('background:ffffff', $url);
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

        self::assertStringContainsString('rotate:90', $url);
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

        self::assertStringContainsString('auto_rotate:1', $url);
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

        self::assertStringContainsString('gravity:ce', $url);
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

        self::assertStringContainsString('size:300:200:1', $url);
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
        self::assertStringContainsString($encodedUrl, $url);
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
        self::assertStringContainsString($encodedUrl, $url);
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
        self::assertStringStartsWith(self::BASE_URL . '/insecure/', $url);

        // Source URL should be properly encoded
        $expectedSourceUrl = self::SOURCE_BASE_URL . '/' . self::SOURCE_PREFIX . '/product.jpg';
        $encodedUrl = rtrim(strtr(base64_encode($expectedSourceUrl), '+/', '-_'), '=');
        self::assertStringContainsString($encodedUrl, $url);
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

        self::assertStringContainsString('resize:fill', $url);
        self::assertStringContainsString('size:300:200', $url);
        self::assertStringContainsString('quality:85', $url);
        self::assertStringContainsString('format:webp', $url);
        self::assertStringContainsString('auto_rotate:1', $url);
    }
}
