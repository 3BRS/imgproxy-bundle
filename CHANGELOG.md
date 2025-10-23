# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-22

### Added
- Initial release
- Imgproxy integration for Liip Imagine Bundle
- Support for 13/17 Liip Imagine filters (76% compatibility)
- URL signing support for security
- S3/CDN source support with configurable prefix
- Static asset detection (skips webpack builds)
- Docker Compose example for local development
- Comprehensive documentation and examples
- PHPStan level max compatibility

### Supported Filters
- thumbnail (outbound/inset modes)
- resize, scale, downscale, upscale
- crop, rotate, auto_rotate
- background, grayscale, watermark
- strip, interlace (automatic)

### Known Limitations
- flip filter not supported (imgproxy limitation)
- paste filter not supported (complex operation)
- relative_resize may be imprecise

[1.0.0]: https://github.com/3brs/imgproxy-bundle/releases/tag/v1.0.0
