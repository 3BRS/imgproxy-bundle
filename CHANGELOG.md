# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.0]: https://github.com/3brs/imgproxy-bundle/releases/tag/v1.0.0
