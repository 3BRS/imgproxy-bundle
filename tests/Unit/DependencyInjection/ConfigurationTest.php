<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use ThreeBRS\ImgproxyBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testConfigTreeBuilderWithMinimalConfiguration(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $processedConfig = $this->processor->processConfiguration(
            $this->configuration,
            [$config]
        );

        $this->assertSame('https://imgproxy.example.com', $processedConfig['base_url']);
        $this->assertSame('https://cdn.example.com', $processedConfig['source_base_url']);
        $this->assertSame('', $processedConfig['source_prefix']);
        $this->assertNull($processedConfig['key']);
        $this->assertNull($processedConfig['salt']);
    }

    public function testConfigTreeBuilderWithFullConfiguration(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
            'source_prefix' => 'media/images',
            'key' => 'abc123',
            'salt' => 'def456',
        ];

        $processedConfig = $this->processor->processConfiguration(
            $this->configuration,
            [$config]
        );

        $this->assertSame('https://imgproxy.example.com', $processedConfig['base_url']);
        $this->assertSame('https://cdn.example.com', $processedConfig['source_base_url']);
        $this->assertSame('media/images', $processedConfig['source_prefix']);
        $this->assertSame('abc123', $processedConfig['key']);
        $this->assertSame('def456', $processedConfig['salt']);
    }

    public function testBaseUrlIsRequired(): void
    {
        $config = [
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/base_url/');

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testSourceBaseUrlIsRequired(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/source_base_url/');

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testBaseUrlCannotBeEmpty(): void
    {
        $config = [
            'base_url' => '',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testSourceBaseUrlCannotBeEmpty(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => '',
        ];

        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testSourcePrefixDefaultsToEmptyString(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $processedConfig = $this->processor->processConfiguration(
            $this->configuration,
            [$config]
        );

        $this->assertArrayHasKey('source_prefix', $processedConfig);
        $this->assertSame('', $processedConfig['source_prefix']);
    }

    public function testKeyAndSaltDefaultToNull(): void
    {
        $config = [
            'base_url' => 'https://imgproxy.example.com',
            'source_base_url' => 'https://cdn.example.com',
        ];

        $processedConfig = $this->processor->processConfiguration(
            $this->configuration,
            [$config]
        );

        $this->assertArrayHasKey('key', $processedConfig);
        $this->assertArrayHasKey('salt', $processedConfig);
        $this->assertNull($processedConfig['key']);
        $this->assertNull($processedConfig['salt']);
    }
}
