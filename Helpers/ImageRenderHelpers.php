<?php

/*
 * version 5
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
        // new factory
        $image_factory = new App\Classes\ImageFactory($original_image_path, $max_height, $max_width, $param);

        // return url
        return $image_factory->getImageUrl();
    }
}
