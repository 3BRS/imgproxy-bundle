<p align="center">
    <a href="https://www.3brs.com" target="_blank">
        <img src="https://3brs1.fra1.cdn.digitaloceanspaces.com/3brs/logo/3BRS-logo-sylius-200.png"/>
    </a>
</p>

<h1 align="center">Imgproxy Bundle for Symfony</h1>

<p align="center">
    <a href="https://packagist.org/packages/3brs/imgproxy-bundle" title="License">
        <img src="https://img.shields.io/packagist/l/3brs/imgproxy-bundle.svg">
    </a>
    <a href="https://packagist.org/packages/3brs/imgproxy-bundle" title="Version">
        <img src="https://img.shields.io/packagist/v/3brs/imgproxy-bundle.svg">
    </a>
    <a href="https://circleci.com/gh/3BRS/imgproxy-bundle" title="Build Status">
        <img src="https://circleci.com/gh/3BRS/imgproxy-bundle.svg?style=shield">
    </a>
</p>

<p align="center">A Symfony bundle that integrates <a href="https://imgproxy.net/">imgproxy</a> with <a href="https://github.com/liip/LiipImagineBundle">Liip Imagine Bundle</a>, replacing on-the-fly image processing with imgproxy's powerful and performant image transformation service.</p>

## Features

- 🚀 **Drop-in replacement** for Liip Imagine Bundle - no template changes needed
- ⚡ **High performance** - leverage imgproxy's native Go implementation
- 🔒 **Secure** - URL signing support to prevent unauthorized image transformations
- 📦 **S3/CDN ready** - works seamlessly with cloud storage
- 🎨 **Full filter support** - supports 13/17 Liip Imagine filters (76% compatibility)
- 🔧 **Smart detection** - automatically skips static assets (webpack builds)

## Requirements

- PHP 8.0 or higher
- Symfony 5.4, 6.x, or 7.x
- Liip Imagine Bundle 2.x
- Running imgproxy server

## Installation

### Step 1: Install the bundle

```bash
composer require 3brs/imgproxy-bundle
```

### Step 2: Register the bundle

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    ThreeBRS\ImgproxyBundle\ThreeBRSImgproxyBundle::class => ['all' => true],
];
```

### Step 3: Configure imgproxy

Create `config/packages/three_brs_imgproxy.yaml`:

```yaml
imports:
    - { resource: "@ThreeBRSImgproxyBundle/Resources/config/packages/three_brs_imgproxy.yaml" }
```

### Step 4: Set environment variables

```bash
# Imgproxy server URL
IMGPROXY_URL=https://imgproxy.your-domain.com

# Source images URL (your S3/CDN endpoint)
IMGPROXY_SOURCE_URL=https://your-bucket.s3.amazonaws.com

# S3 prefix path (optional, leave empty if images are in bucket root)
IMGPROXY_SOURCE_PREFIX=media/image

# Optional: URL signing keys
IMGPROXY_KEY=your_hex_key_here
IMGPROXY_SALT=your_hex_salt_here
```

### Step 5: Update Liip Imagine configuration

In `config/packages/liip_imagine.yaml`, change the cache resolver:

```yaml
liip_imagine:
    # ... other config
    cache: imgproxy  # Change from 'default' or your current resolver
```

### Step 6: Clear cache

```bash
php bin/console cache:clear
```

### Production deployment

See [imgproxy documentation](https://docs.imgproxy.net/) for Kubernetes, AWS ECS, or other deployment options.

## Filter Compatibility

### ✅ Fully Supported (13/17 filters)

| Liip Imagine Filter | Imgproxy Equivalent | Notes |
|---------------------|---------------------|-------|
| `thumbnail` | `resize:fill` or `resize:fit` | Most commonly used |
| `resize` | `resize:force` + `size` | Exact dimensions |
| `scale` | `resize:fit` + `size` | Proportional scaling |
| `downscale` | `resize:fit` + `enlarge:0` | Never enlarges |
| `upscale` | `resize:fit` + `enlarge:1` | Always enlarges |
| `crop` | `resize:fill` + `size` | Crop to dimensions |
| `rotate` | `rotate` | Angle in degrees |
| `auto_rotate` | `auto_rotate:1` | EXIF-based rotation |
| `background` | `background` + `extend` | Background color |
| `grayscale` | `saturation:0` | Grayscale effect |
| `watermark` | `watermark` | Requires URL |
| `strip` | ✓ | Automatic |
| `interlace` | ✓ | Automatic |

### ⚠️ Partially Supported

- `relative_resize` - May be imprecise (requires source dimensions)

### ❌ Not Supported

- `flip` - No native imgproxy support (use CSS `transform`)
- `paste` - Complex compositing not supported
- `resample` - DPI changes irrelevant for web

## Usage

No changes to your templates are needed! All existing `imagine_filter` calls work automatically:

```twig
{# Works exactly as before #}
<img src="{{ asset('/media/image.jpg')|imagine_filter('thumbnail') }}" />

{# With responsive images #}
<picture>
    <source srcset="{{ asset('/media/image.jpg')|imagine_filter('thumbnail_webp') }}" type="image/webp">
    <img src="{{ asset('/media/image.jpg')|imagine_filter('thumbnail') }}" />
</picture>
```

### How It Works

```
┌─────────────────┐
│  Twig Template  │
│  imagine_filter │
└────────┬────────┘
         │
         v
┌─────────────────────┐
│   Imgproxy Bundle   │
│  (Cache Resolver)   │
└────────┬────────────┘
         │
         v
┌─────────────────────┐      ┌──────────────┐
│  Imgproxy Server    │◄─────│  S3 / CDN    │
│  (Image Processing) │      │  (Source)    │
└────────┬────────────┘      └──────────────┘
         │
         v
┌─────────────────────┐
│   Generated URL     │
│   (Signed/Unsigned) │
└─────────────────────┘
```

**Example Generated URLs:**

```
# Without signing (development)
https://imgproxy.example.com/insecure/resize:fill:300:200/plain/s3-bucket.com/image.jpg

# With signing (production)
https://imgproxy.example.com/AbC123.../resize:fill:300:200/plain/s3-bucket.com/image.jpg
```

### Performance Benefits

Using imgproxy provides significant performance improvements:

- ⚡ **Native Go Performance** - Up to 10x faster than PHP-based image processing
- 🔄 **On-the-fly Processing** - No need to pre-generate image variations
- 💾 **Reduced Storage** - Store only original images, generate variants on demand
- 🌐 **CDN Compatible** - imgproxy URLs can be cached by CDN for global distribution

## Static Assets

The bundle automatically detects and skips static assets (webpack builds, bundles):

- `/build/**` - webpack encore assets
- `/bundles/**` - Symfony bundle assets
- `/assets/**` - general static files

These are returned without imgproxy processing.

## Development

### Setting up Development Environment

1. Clone the repository:
```bash
git clone https://github.com/3BRS/imgproxy-bundle.git
cd imgproxy-bundle
```

2. Install dependencies:
```bash
composer install
```

3. Start Docker environment:
```bash
make up
```

### Available Make Commands

Run `make help` to see all available commands:

```bash
make install          # Install composer dependencies
make test             # Run all tests (ECS + PHPStan)
make ecs              # Check code style
make ecs-fix          # Fix code style issues
make phpstan          # Run static analysis
make bash             # Connect to PHP container
```

## Testing

Run the complete test suite:

```bash
# Run all quality checks
make test

# Or run individually
make ecs              # Code style check
make phpstan          # Static analysis
```

### Continuous Integration

The project uses CircleCI to test against multiple PHP and Symfony versions:

- **PHP versions**: 8.0, 8.1, 8.2, 8.3, 8.4
- **Symfony versions**: 5.4, 6.4, 7.1
- **Dependency strategies**: `--prefer-lowest` and `--prefer-stable`

## Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`make test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Code Quality Standards

- Follow PSR-12 coding standards
- Ensure PHPStan passes at level 8
- Write meaningful commit messages
- Add tests for new features

## License

This project is licensed under the MIT License.

## Credits

Developed and maintained by [3BRS](https://3brs.com)

## Support

- 🐛 [Report bugs](https://github.com/3BRS/imgproxy-bundle/issues)
- 💡 [Request features](https://github.com/3BRS/imgproxy-bundle/issues)
- 📖 [Imgproxy Documentation](https://docs.imgproxy.net/)
- 📖 [Liip Imagine Bundle Documentation](https://symfony.com/bundles/LiipImagineBundle/current/index.html)
