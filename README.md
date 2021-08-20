# Image renderer for Laravel

## What is it?

*Work in Progress*

## Donate

Find this project useful? You can support me on Patreon

https://www.patreon.com/pixsil

## Installation

For a quick install, run this from your project root:
```bash
mkdir -p app/Classes
wget -O app/Classes/Images/ImageFactory.php https://raw.githubusercontent.com/pixsil/xxxx??
wget -O app/Http/Controllers/ImageRenderController.php https://raw.githubusercontent.com/pixsil/xxxx??
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
