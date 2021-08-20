<?php

// version 2.1

namespace App\Traits;

use App\Classes\ImageFactory;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

trait ImageTrait
{
    /**
     * get storage file path
     * $identifier = for default: $height _ $with for curstom: hash
     * example: /home/vagrant/code/site/storage/app/private/projects/1/background_image/image_bg_500_250.jpg
     */
    public function getStorageImageFilePath_2($field, $identifier, $public = false)
    {
        // must save first to receive id
        if (!$id = $this->id) {
            return null;
        }
        // guard if field excist
        if (!$value = $this->$field) {
            return null;
        }

        // is it public or private folder
        $folder = 'private';
        if ($public) {

            // set public
            $folder = 'public';
        }

        // get the pathinfo
        $pathinfo = pathinfo($value);

        // generate new image name
        $image_storage_name = $pathinfo['filename'] .'_'. $identifier .'.'. $pathinfo['extension'];

        // set path and filename
        $image_storage_path = storage_path('app/'. $folder) .'/'. $this->getTable() .'/'. $id .'/'. $field .'/'. $image_storage_name;

        return $image_storage_path;
    }

    /*
     * create image
     *
     * parameters:
     * default = crop / keep-aspect-ratio / no-upscale / optimize
     *
     * f = change crop to fit
     * s = change keep-aspect-ratio to strach
     * u = change no-upscale to upscale
     * r = change optimize to no-optimize
     */
    protected function createImage_2($field, $max_width, $max_height, $image_storage_path, $param = null, $callback = null, $public = false)
    {
        // get storage path
        $filepath = $this->getStorageFilePath($field, $public);

        // allow some more ram
        // ini_set('memory_limit', '384M');

        // get image
        $image = Image::make($filepath);

        // do image stuff
        if (!is_callable($callback)) {

            // if fit
            if (strpos($param,'f')) {

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
                $image->resize($max_width_nullable, $max_height_nullable, function ($constraint) use ($param) {

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

            // do callback
            call_user_func($callback, $image);
        }

        // save the image
        $image->save($image_storage_path);

        // save the image
        if (!strpos($param,'r')) {

            // optimize
            ImageOptimizer::optimize($image_storage_path);
        }

        return $image;
    }

    /**
     * deprecated
     */
    public function getImage($field, $width, $height, $callback = null, $public = false)
    {
        return $this->getOrCreateImageObject($field, $width, $height, $callback = null, $public = false);
    }

    /**
     * get back the image object to serve
     * this functions save the image first (also aplying compression) not smart to make a object from it again
     * so why the save?
     */
    public function _getImageObject_2($field, $width, $height, $param = null, $callback = null, $public = false)
    {
        // guard if file excist
        if (!$this->fileExists($field, $public)) {
            return false;
        }
        // guard identiefier
        if (!$identifier = !$callback ? $height .'_'. $width .'_'. $param : substr(md5(json_encode($callback)),0,8)) {
            return '';
        }
        // gauard: if name from database fields cannot be created
        if (!$image_storage_path = $this->getStorageImageFilePath_2($field, $identifier, $public)) {
            return null;
        }

        // guard if file not excist
        if (!File::exists($image_storage_path)) {

            // create image and give back object
            $image = $this->createImage_2($field, $width, $height, $image_storage_path, $param, $callback, $public);

        // already excist just serve
        } else {

            // get image
            $image = Image::make($image_storage_path);
        }

        return $image;
    }

    /**
     * get back the image object to serve
     * for public images
     */
    public function getImageUrl_2($field, $max_width, $max_height, $param = 0, $callback = null)
    {
        // gaurd must save first to receive id
        if (!$id = $this->id) {
            return null;
        }
        // guard if field excist
        if (!$value = $this->$field) {
            return false;
        }
        // guard if file excist
        // if (!$this->fileExists($field, true)) {
            // return '';
        // }
        // guard identiefier
        // if (!$identifier = !$callback ? $height .'_'. $width .'_'. $param : substr(md5(json_encode($callback)),0,8)) {
            // return '';
        // }
        // guard if name from database fields cannot be created
        // if (!$image_storage_path = $this->getStorageImageFilePath_2($field, $identifier, true)) {
            // return '';
        // }

        // guard if file not excist
        // if (env('CACHE_IMAGES', true) === false || !File::exists($image_storage_path)) {

            // void create image

        //
        $public_path = 'storage/'. $this->getTable() .'/'. $id .'/'. $field .'/'. $value;

        // get image url
        $url = ImageFactory::getImageUrl($public_path, $max_width, $max_height, $param);
            // $this->createImage_2($field, $height, $width, $image_storage_path, $param, $callback, true);
        // }

        // get public image path
        // $url = $this->getPublicImageFilePath_2($field, $identifier);

        return $url;
    }
}
