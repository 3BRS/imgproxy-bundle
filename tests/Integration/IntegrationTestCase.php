<?php

declare(strict_types=1);

namespace ThreeBRS\ImgproxyBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ThreeBRS\ImgproxyBundle\Tests\Integration\App\TestKernel;

/**
 * Base class for integration tests
 */
abstract class IntegrationTestCase extends TestCase
{
    protected ?TestKernel $kernel = null;
    protected ?ContainerInterface $container = null;

    /** @var callable|null */
    private $originalExceptionHandler;

    /** @var callable|null */
    private $originalErrorHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original handlers
        $this->originalExceptionHandler = set_exception_handler(static function (\Throwable $e): void {
            throw $e;
        });
        restore_exception_handler();

        $this->originalErrorHandler = set_error_handler(static fn(int $errno, string $errstr): bool => false);
        restore_error_handler();

        $this->cleanupCache();
    }

    protected function tearDown(): void
    {
        if ($this->kernel instanceof TestKernel) {
            $this->kernel->shutdown();
            $this->kernel = null;
            $this->container = null;
        }

        // Restore all error handlers that might have been set during the test
        // Symfony kernel may set multiple error handlers, so restore all of them
        while (true) {
            $handler = set_error_handler(static fn() => null);
            restore_error_handler();

            if ($handler === null) {
                break;
            }

            restore_error_handler();
        }

        // Restore all exception handlers
        while (true) {
            $handler = set_exception_handler(static fn() => null);
            restore_exception_handler();

            if ($handler === null) {
                break;
            }

            restore_exception_handler();
        }

        // Restore original handlers if we saved them
        if ($this->originalErrorHandler !== null) {
            set_error_handler($this->originalErrorHandler);
        }

        if ($this->originalExceptionHandler !== null) {
            set_exception_handler($this->originalExceptionHandler);
        }

        $this->cleanupCache();
        parent::tearDown();
    }

    /**
     * Boot kernel with custom bundle configuration
     */
    protected function bootKernel(array $bundleConfig = []): void
    {
        $this->kernel = new TestKernel('test', true);

        if ($bundleConfig !== []) {
            $this->kernel->setBundleConfig($bundleConfig);
        }

        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
    }

    /**
     * Get service from container
     */
    protected function getService(string $id): ?object
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new \RuntimeException('Container is not available. Call bootKernel() first.');
        }

        return $this->container->get($id);
    }

    /**
     * Check if service exists in container
     */
    protected function hasService(string $id): bool
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new \RuntimeException('Container is not available. Call bootKernel() first.');
        }

        return $this->container->has($id);
    }

    /**
     * Get parameter from container
     */
    protected function getParameter(string $name): mixed
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new \RuntimeException('Container is not available. Call bootKernel() first.');
        }

        return $this->container->getParameter($name);
    }

    /**
     * Cleanup cache directory
     */
    private function cleanupCache(): void
    {
        $cacheDir = __DIR__ . '/../../var/cache/test';
        if (is_dir($cacheDir)) {
            $this->removeDirectory($cacheDir);
        }
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
