# Image renderer for Laravel

## What is it and how does it work

- This library makes optimized images for your Laravel application.
- The images are minimized using the Spatie image compressor
- The images are build and cached based on the needed format and crop
- You can choose a filesystem to use for the storage of the original files or de cached files, also AWS S3 for example
- Library uses the filesystem url to give the images back to the client

## Donate

Find this project useful? You can support me with a Paypal donation:

[Make Paypal Donation](https://www.paypal.com/donate/?hosted_button_id=2XCS6R3CTC5BA)

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
mkdir -p app/Console/Commands
wget -O app/Console/Commands/CleanImageCacheCommand.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Commands/CleanImageCacheCommand.php
mkdir -p app/Traits
wget -O app/Traits/ImageTrait.php https://raw.githubusercontent.com/pixsil/image-renderer/main/Traits/ImageTrait.php
mkdir -p storage/app/db
```

Add to env

Images are rendered with an direct url, so an storage disk must provide a way to load the images with an direct url. Public always works. For AWS or FTP an url must be set inside the filesystems.php.

```
REGENERATE_IMAGES="false"
SECURE_IMAGES="true"
#IMAGE_STORAGE_DISK="db" // default option
#IMAGE_CACHE_DISK="cache" 
```

When using these disks, make sure you have them:

```
// dont need to have a public one, all files going served, all images are cached in different folder
'db' => [
    'driver' => 'local',
    'root' => storage_path('app/db'),
],

'cache' => [
    'driver' => 'local',
    'root' => storage_path('app/cache'),
    'url' => env('APP_URL').'/cache',
    'visibility' => 'public',
    'throw' => false,
],
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

// Add the trait to the model you want to use
```
use ImageTrait;
```

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
