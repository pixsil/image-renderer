# Image renderer for Laravel

## What is it and how does it work

*Work in Progress*

This library makes optimalized images for your Laravel application. The library renders an link for the image href for each image and it is not doing any image logic with the main request what makes this library super fast. Inside the url some parameters are given, for crobs, sizes and some effects.

When the browser start loading the images the url, the request url checks if the image got an cached. If this is the case the cached image will be returned. If the cached image is not yet created the library creates this image and applies the settings (crob etc) that are given in the link.

This library is made to make use of filesystems. For example you can use it to use in comibation with a Amazon S3 bucket.

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
wget -O app/Http/Controllers/ImageRenderController.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Controllers/ImageRenderController.php
mkdir -p app/Helpers
wget -O app/Http/Helpers/ImageRenderHelpers.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Helpers/ImageRenderHelpers.php
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

Add to your general helper file
```
include('Helpers/ImageRenderHelpers.php');
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

## Not working?

- This library is using the url set in your fileservers config. Make sure this url is correct and the symlink is in place.
- This library using the APP_URL to generate the image link (if using local storage). Make sure you got the url correct. When testing without an SSL certificate. Make sure the APP_URL does not have https but http.
- Also check if you have to right env variables
