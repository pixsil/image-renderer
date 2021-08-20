<?php

// version 3

namespace App\Http\Controllers;

use App\Classes\ImageFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ImageRenderController extends Controller
{
    /*
     * parameters:
     * default = crop / keep-aspect-ratio / no-upscale / optimized
     *
     * f = change crop to fit
     * s = change keep-aspect-ratio to strach
     * u = change no-upscale to upscale
     * r = change optimized to not-optimized
     */
    public function image($max_width, $max_height, $param = 0, $hash, $public_path)
    {
        // guard gen hash
        if (!$public_path = substr($public_path, 0, -2)) {
            return abort(404);
        }
        // guard gen hash
        if (!$our_hash = substr(md5($public_path . $max_width . $max_height . $param), 0, 8)) {
            return abort(404);
        }
        // check hash if securtiy is on
        if (env('SECURE_IMAGES', true) === true && $our_hash !== $hash) {
            return abort(404);
        }
        // file dont exist
        if (!File::exists(public_path($public_path))) {
            return abort(404);
        }

        // get the pathinfo
        $pathinfo = pathinfo($public_path);

        // set uniquefier
        $identifier = $max_width .'_'. $max_height  .'_'. $param;

        // generate new image name
        $image_name = md5($pathinfo['dirname'] .'_'. $pathinfo['filename'] .'_'. $identifier) .'.'. $pathinfo['extension'];

        // set new url to give back
        $new_url = '/storage/img_cache/'. $image_name;

        // get path
        $storage_filepath = storage_path('app/public/img_cache/'. $image_name);

        // guard if file not exist
        if (env('CACHE_IMAGES', true) === false || !File::exists($storage_filepath)) {

            // create image
            ImageFactory::createImage($public_path, $max_width, $max_height, $param);
        }

        // make respone
        if (env('CACHE_IMAGES', true) === true) {

            // set default redirect to new url
            $respone = redirect($new_url);

        // debuging
        } else {
            // dont redirect and serve file for making debuging easy
            $respone = response()->file($storage_filepath);
        }

        return $respone;
    }
}
