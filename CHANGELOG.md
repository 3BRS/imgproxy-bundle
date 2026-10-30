# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-30

### Added
- Symfony 8 support (requires PHP 8.4+ and Liip Imagine Bundle 2.16+)
- PHP 8.5 support

### Changed
- CI moved from CircleCI to GitHub Actions
- CI tests PHP 8.1–8.5 with Symfony 5.4, 6.4, 7.4 and 8.x; Symfony 7.4 replaces 7.1 in the matrix
- CI restricts all Symfony components to the tested Symfony version with Symfony Flex (`SYMFONY_REQUIRE`)
- Development tools: PHPStan 2, Rector 2, ECS 13, Deptrac 2–4, PHPUnit 10.5+
- `phpunit.xml.dist` uses the PHPUnit 10.5 schema and tests declare data providers with attributes
- Docker development image uses PHP 8.5

### Removed
- PHP 8.0 support: PHP 8.0 reached end of life in November 2023, and Twig releases without security advisories require PHP 8.1. PHP 8.0 is supported by the 1.0.x releases.

### Technical Requirements
- PHP 8.1, 8.2, 8.3, 8.4, or 8.5
- Symfony 5.4, 6.x, 7.x, or 8.x
- Liip Imagine Bundle ^2.0

## [1.0.0] - 2024-10-29

### Added
- Initial release of Imgproxy Bundle for Symfony
- Drop-in replacement for Liip Imagine Bundle with imgproxy integration
- ImgproxyUrlBuilder service for generating imgproxy URLs
- ImgproxyCacheResolver implementing Liip Imagine ResolverInterface
- FilterConfigConverter for translating Liip Imagine filters to imgproxy options
- Support for 13 out of 17 Liip Imagine filters (76% compatibility)
- URL signing support with HMAC SHA256 for secure image delivery
- S3/CDN source support with configurable base URL and prefix
- Automatic static asset detection (skips webpack builds, bundles, etc.)
- Base64 URL encoding option for special characters
- Quality and enlargement settings per filter
- Comprehensive documentation with examples
- Docker Compose example for local development

### Supported Filters
- **thumbnail** - with outbound (fill) and inset (fit) modes
- **resize** - exact dimensions
- **scale** - proportional scaling
- **downscale** - scale down only
- **upscale** - scale up only
- **crop** - with position and size
- **rotate** - arbitrary angles
- **auto_rotate** - based on EXIF orientation
- **background** - background color
- **grayscale** - convert to grayscale
- **watermark** - overlay images with position and opacity
- **strip** - remove metadata
- **interlace** - automatic progressive JPEG

### Known Limitations
- **flip** filter not supported (imgproxy limitation)
- **paste** filter not supported (complex operation)
- **relative_resize** may be imprecise (converted to absolute)
- **colorspace** filter not implemented (rarely used)

### Technical Requirements
- PHP 8.0, 8.1, 8.2, 8.3, or 8.4
- Symfony 5.4, 6.4, or 7.1
- Liip Imagine Bundle ^2.0
- Imgproxy server (separate service)

### Quality Assurance
- 106 tests with 381 assertions (100% passing)
- Full integration test suite with Symfony kernel
- PHPStan level 8 (strict static analysis)
- ECS code style compliance
- Rector quality checks
- Deptrac architecture validation
- Infection mutation testing
- CI/CD pipeline with CircleCI testing all PHP/Symfony combinations

### Documentation
- Complete README with installation and configuration guide
- Detailed filter compatibility matrix
- Configuration examples for common use cases
- Docker Compose setup for local development
- Integration test examples

[1.1.0]: https://github.com/3brs/imgproxy-bundle/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/3brs/imgproxy-bundle/releases/tag/v1.0.0
