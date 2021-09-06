<?php

/*
 * version 7
 *
 * parameters:
 * default = crop / keep-aspect-ratio / no-upscale
 *
 * f = change crop to fit
 * s = change keep-aspect-ratio to strach
 * u = change no-upscale to upscale
 */
if (!function_exists('img_url')) {
    function img_url($original_image_path, $max_height, $max_width, $param = 0)
    {
        // init
        $url = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D';

        // new factory
        $image_factory = App\Classes\ImageFactory::create($original_image_path, $max_width, $max_height, $param)
            ->generateOptimizedImagePath();

        // return url
        if ($image_url = $image_factory->getImageUrl()) {
            $url = $image_url;
        }

        return $url;
    }
}

