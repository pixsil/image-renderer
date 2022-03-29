# Image renderer for Laravel

## What is it?

*Work in Progress*

## Donate

Find this project useful? You can support me on Patreon

https://www.patreon.com/pixsil

## Requirements

This tool is using the packages intervention/image and spatie/image-optimizer

```
composer require intervention/image
composer require spatie/laravel-image-optimizer
```

This package also depends on pixsil/storage-trait:

https://github.com/pixsil/storage-trait

## Installation

For a quick install, run this from your project root:
```bash
mkdir -p app/Classes/ImageFactory
wget -O app/Classes/ImageFactory/ImageFactory.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Classes/ImageFactory/ImageFactory.php
mkdir -p app/Commands
wget -O app/Commands/CleanStorageCacheCommand.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Commands/CleanStorageCacheCommand.php
wget -O app/Controllers/Http/ImageRenderController.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Controllers/ImageRenderController.php
mkdir -p app/Helpers
wget -O app/Helpers/ImageRenderHelpers.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Helpers/ImageRenderHelpers.php
mkdir -p app/Traits
wget -O app/Traits/ImageTrait.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Traits/ImageTrait.php
```

Add to env
```
REGENERATE_IMAGES="false"
SECURE_IMAGES="true"
#IMAGE_STORAGE_DISK="ftp"
#IMAGE_CACHE_DISK="public"
```

Add to routes
```
// image renderer
Route::get('/render/{width}/{height}/{param}/{filename}/r', [ImageRenderController::class, 'image'])->where('filename', '(.*)')->name('image-render');
```

## Usage

// geting the url for rendering the image
// it can give back two types of urls
// an render image url (if image don’t exists)
// or and direct url
$object->getImageUrl_2()

// filename in public
```html
<Img src="{{ img_url('/images_html/me.png', 168, 183) }}">
```

// filename for record

```html
<img src="{{ $company->getImageUrl_2('logo_image', 208, 104) }}"/>
```

## Additional knowledge

## Example
