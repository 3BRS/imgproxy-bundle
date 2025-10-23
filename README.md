# Imgproxy Bundle for Symfony

A Symfony bundle that integrates [imgproxy](https://imgproxy.net/) with [Liip Imagine Bundle](https://github.com/liip/LiipImagineBundle), replacing on-the-fly image processing with imgproxy's powerful and performant image transformation service.

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

### Step 3: Set environment variables

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

### Step 4: Update Liip Imagine configuration

In `config/packages/liip_imagine.yaml`, change the cache resolver:

```yaml
liip_imagine:
    # ... other config
    cache: imgproxy  # Change from 'default' or your current resolver
```

### Step 5: Clear cache

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

## Static Assets

The bundle automatically detects and skips static assets (webpack builds, bundles):

- `/build/**` - webpack encore assets
- `/bundles/**` - Symfony bundle assets
- `/assets/**` - general static files

These are returned without imgproxy processing.

## License

MIT

## Credits

Developed by [3BRS](https://3brs.com)
