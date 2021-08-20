<?php

// version 5

Namespace App\Classes;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Menu factory
 */
class ImageFactory
{
    /*
     * get image url
     * 
     * this one get always loaded by image request, this must be fast
     * public path because you never want to cache protected images
     */
    static public function getImageUrl($public_path, $max_height, $max_width, $param = 0)
    {
        // guard trim
        if (!$public_path = trim($public_path, '/')) {
            return null;
        }
        // guard public path
        if (!$pathinfo = pathinfo($public_path)) {
            return null;
        }
        // guard filename
        if (!isset($pathinfo['filename'])) {
            return null;
        }
        // guard extension
        if (!isset($pathinfo['extension'])) {
            return null;
        }

        // replace slashes
        $public_path = trim($public_path, '/');

        // get the pathinfo
        $pathinfo = pathinfo($public_path);

        // set uniquefier
        $identifier = $max_height .'_'. $max_width  .'_'. $param;

        // generate new image name
        $image_name = md5($pathinfo['dirname'] .'_'. $pathinfo['filename'] .'_'. $identifier) .'.'. $pathinfo['extension'];

        // set new url to give back
        $new_url = '/'. $pathinfo['dirname'] .'/cache/'. $image_name;

        // get path
        $storage_filepath = public_path($new_url);

        // guard if file not excist
        if (env('CACHE_IMAGES', true) === false || !File::exists($storage_filepath)) {

            // 
            $new_url = self::getHashUrl($public_path, $max_width, $max_height, $param);
        }

        return $new_url;
    }

    /*
     * Create image
     */
    static public function createImage($public_path, $max_width, $max_height, $param = 0, $callback = null)
    {
        // get the pathinfo
        $pathinfo = pathinfo($public_path);

        // set uniquefier
        $identifier = $max_height .'_'. $max_width  .'_'. $param;

        // generate new image name
        $image_name = md5($pathinfo['dirname'] .'_'. $pathinfo['filename'] .'_'. $identifier) .'.'. $pathinfo['extension'];

        // get path
        $storage_path = public_path($pathinfo['dirname'] .'/cache/');
        $storage_filepath = public_path($pathinfo['dirname'] .'/cache/'. $image_name);

        // check and create path
        if (!File::exists($storage_path)) {
            mkdir($storage_path, 0755, true);
        }

        // allow some more ram
        ini_set('memory_limit', '384M');

        // get image
        $image = Image::make(public_path($public_path));

        // do image stuff
        if (!is_callable($callback)) {

            // if fit
            if (strpos($param,'f') !== false) {
        
                // fit image
                $image->fit($max_width, $max_height, function ($constraint) use ($param) {


                    // if upscale allowed
                    if (strpos($param,'u') === false) {


                        // prevent from upscaling
                        $constraint->upsize();
                    }
                });

            // if normal
            } else {

                // nullable
                $max_height_nullable = $max_height == 0 ? null : $max_height;
                $max_width_nullable = $max_width == 0 ? null : $max_width;

                // default do the resize
                $image->resize($max_height_nullable, $max_width_nullable, function ($constraint) use ($param) {

                    // if stratch allowed
                    if (strpos($param,'s') === false) {


                        // keep aspect, otherwise it get strached
                        $constraint->aspectRatio();
                    }

                    // if upscale allowed
                    if (strpos($param,'u') === false) {

                        // prevent from upscaling
                        $constraint->upsize();
                    }
                });
            }

        // use custom function
        } else {

            // use
            call_user_func($callback, $image);
        }

        // save the image
        $image->save($storage_filepath);

        // save the image
        if (strpos($param,'r') === false) {

            // create chain factory
            $optimizerChain = OptimizerChainFactory::create();

            // optimize
            $optimizerChain->optimize($storage_filepath);
        }

        return;
    }


    /*
     * get image hash image
     */
    static public function getHashUrl($public_path, $max_width, $max_height, $param = 0)
    {    
        // make hash
        $hash = md5($public_path . $max_width . $max_height . $param);

        // generate image url
        $image_url = '/render/'. $max_width .'/'. $max_height .'/'. $param .'/'. substr($hash, 0, 8) .'/'. $public_path .'/r';

        return $image_url;
    }
}
