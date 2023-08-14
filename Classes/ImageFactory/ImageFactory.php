<?php

// version 9

Namespace App\Classes\ImageFactory;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationData;
use Intervention\Image\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

/**
 * Menu factory
 */
class ImageFactory
{
    public $original_image_path;
    public $pathinfo;
    public $max_width;
    public $max_height;
    public $param;
    public $storage_disk;
    public $cache_disk;
    public $override_storage_disk;
    public $override_cache_disk;
    public $timestamp;
    public $crop_x;
    public $crop_y;
    public $crop_width;
    public $crop_height;
    public $optimized_path;

    /*
     * create instance
     */
    static public function create($original_image_path, $max_width, $max_height, $param = 0)
    {
        return new self($original_image_path, $max_width, $max_height, $param);
    }

    /*
     * parameters:
     * default = crop / keep-aspect-ratio / no-upscale / optimize
     *
     * f = change crop to fit
     * s = change keep-aspect-ratio to strach
     * u = change no-upscale to upscale
     * r = change optimize to no-optimize
     */
    public function __construct($original_image_path, $max_width, $max_height, $param = 0)
    {
        $this->original_image_path = trim($original_image_path, '/');
        $this->pathinfo = pathinfo($original_image_path);
        $this->max_width = $max_width;
        $this->max_height = $max_height;
        $this->param = $param;

        $this->storage_disk = Storage::disk(env("IMAGE_STORAGE_DISK", 'db'));
        $this->cache_disk = Storage::disk(env("IMAGE_CACHE_DISK", 'public'));

        return $this;
    }

    /*
     * get image url
     * Used by storage/helper
     *
     * this one get always loaded by image request, this must be fast
     * it does not matter if the optimized image is there or not
     */
    public function _getImageUrl()
    {
        // guard filename
        if (!isset($this->pathinfo['filename'])) {
            return false;
        }
        // guard extension
        if (!isset($this->pathinfo['extension'])) {
            return false;
        }
        // guard optimized path
        if (!$optimized_path = $this->optimized_path) {
            return false;
        }

        // this is just if we give back the url to render or rerender
        // or we go directly to the image in the storage
        if (env('REGENERATE_IMAGES', false) === true || !$this->cache_disk->has($optimized_path)) {
            // optimalize image
            $render_url = $this->getRenderUrl();

        } else {

            // go to cached version
            $render_url = $this->getCachedUrl($optimized_path);
        }

        return $render_url;
    }

    /*
     * get image hash image
     */
    public function getCachedImagePath()
    {
        // guard
        if (!$dirname = $this->pathinfo['dirname'] ?? null) {
            return null;
        }
        // guard
        if (!$filename = $this->pathinfo['filename'] ?? null) {
            return null;
        }
        // guard
        if (!$extension = $this->pathinfo['extension'] ?? null) {
            return null;
        }

        // just make this identifier with variables from the render function
        // if the user changes (like crop etc) are changed, this should trigger an full delete (its is like the source changed)
        $identifier_arr = [
            env('APP_KEY'),
            $filename,
            $this->max_width,
            $this->max_height,
            $this->param,
            $this->timestamp,
            $this->crop_x,
            $this->crop_y,
            $this->crop_width,
            $this->crop_height,
        ];

        // set identifier
        $identifier = implode('_', $identifier_arr);

        // generate new image name
        $hash = substr(md5($identifier), 0, 6);

        $cached_filename = $filename .'.'. $extension;

        $param_url_part = $this->param ? '_'. $this->param : '';

        $sub_cache_folder = "{$this->max_width}_{$this->max_height}{$param_url_part}_$hash";

        // set new url to give back
        $cached_path = $dirname .'/cache/'. $sub_cache_folder .'/'. $cached_filename;

        return $cached_path;
    }

    /*
     * get image hash image
     */
    public function getCachedUrl($optimized_path = null)
    {
        // guard optimized path
        if (!$optimized_path = $this->optimized_path) {
            abort(403, 'no optimized path is set2');
        }

        //
        $url_to_file = $this->cache_disk->url($optimized_path);

        return $url_to_file;
    }

    /*
     * get image hash image
     */
    public function _getRenderUrl()
    {
        //
        $default_parameters = [
            'width' => $this->max_width,
            'height' => $this->max_height,
            'param' => $this->param,
            'filename' => $this->original_image_path,
        ];

        // add crop parameters
        $crop_parameters = [];
        if ($this->needToCrop()) {
            // set
            $crop_parameters = [
                'crop_x' => $this->crop_x,
                'crop_y' => $this->crop_y,
                'crop_width' => $this->crop_width,
                'crop_height' => $this->crop_height,
            ];
        }

        $timestamp_parameter = [];
        if ($this->timestamp) {
            // set
            $timestamp_parameter = [
                'timestamp' => $this->timestamp,
            ];
        }

        $storage_parameter = [];
        if ($this->override_storage_disk) {
            // set
            $storage_parameter = [
                'storage' => $this->override_storage_disk,
            ];
        }

        $cache_parameter = [];
        if ($this->override_cache_disk) {
            // set
            $cache_parameter = [
                'cache' => $this->override_cache_disk,
            ];
        }

        //
        $url = URL::temporarySignedRoute('image-render', now()->addMinutes(1), array_merge($default_parameters, $crop_parameters, $timestamp_parameter, $storage_parameter, $cache_parameter));

        return $url;
    }

    /*
     * Create image if not exists or need to remake
     */
    public function setCrop($crop_x, $crop_y, $crop_width, $crop_height)
    {
        $this->crop_x = $crop_x;
        $this->crop_y = $crop_y;
        $this->crop_width = $crop_width;
        $this->crop_height = $crop_height;

        return $this;
    }

    /*
     * Create image if not exists or need to remake
     */
    public function setTimestamp($timestamp)
    {
        //
        $this->timestamp = $timestamp;

        return $this;
    }

    /*
     * Create image if not exists or need to remake
     */
    public function generateCachedImagePath()
    {
        // set
        $this->optimized_path = $this->getCachedImagePath();

        return $this;
    }

    /*
     * create save and work like a pro
     */
    public function createImage($callback = null)
    {
        // guard original file dont exist
        if (!$this->storage_disk->has($this->original_image_path)) {
            return abort(404, 'could not find image');
        }
        // guard
        if (!$dirname = $this->pathinfo['dirname']) {
            abort('403', 'Incorrect image directory');
        }
        // guard optimized path
        if (!$optimized_path = $this->optimized_path) {
            abort(403, 'no optimized path is set3');
        }

        // remove all the old cache
        // cannot delete cache because you can have cache of different sizes
        // we have a cleanup function in treat, better place also
        // $this->cache_disk->deleteDirectory($dirname .'/cache');

        //
        $file = $this->storage_disk->get($this->original_image_path);

        // allow some more ram
        ini_set('memory_limit', '384M');

        // get image
        $image = Image::make($file);

        // we we have to crop do it
        if ($this->needToCrop()) {
            $image->crop($this->crop_width, $this->crop_height, $this->crop_x, $this->crop_y);
        }

        // do image stuff
        if (!is_callable($callback)) {

            // if fit
            if (strpos($this->param,'f') !== false) {

                // fit image
                $image->fit($this->max_width, $this->max_height, function ($constraint) {

                    // if upscale allowed
                    if (strpos($this->param,'u') === false) {

                        // prevent from upscaling
                        $constraint->upsize();
                    }
                });

                // if normal
            } else {

                // nullable
                $max_height_nullable = $this->max_height == 0 ? null : $this->max_height;
                $max_width_nullable = $this->max_width == 0 ? null : $this->max_width;

                // default do the resize
                $image->resize($max_width_nullable, $max_height_nullable, function ($constraint) {

                    // if stratch allowed
                    if (strpos($this->param,'s') === false) {


                        // keep aspect, otherwise it get strached
                        $constraint->aspectRatio();
                    }

                    // if upscale allowed
                    if (strpos($this->param,'u') === false) {

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

        // if we want to opimilize first write a temp file
        // it must be done by commandline with a saved image
        if (strpos($this->param,'r') === false) {

            // get tmp filename
            $tmp_file = tempnam(sys_get_temp_dir(), 'myApp_');

            // image save to tmp dir
            $image->save($tmp_file);

            //
            ImageOptimizer::optimize($tmp_file);

            // laod tmp file back to image processor
            $this->cache_disk->put($optimized_path, file_get_contents($tmp_file));

            // dont upload
        } else {

            // needed to save the file directly as steam (directly into the storage)
            $image->encode();

            // save the image
            $this->cache_disk->put($optimized_path, $image);
        }

        return;
    }

    // /*
    //  * check if we need to create an optimized image
    //  */
    // public function _needToCreate()
    // {
    //     // guard optimized path
    //     if (!$optimized_path = $this->optimized_path) {
    //         abort(403, 'no optimized path is set4');
    //     }
    //     // guard no file always create
    //     if (!$this->cache_disk->has($optimized_path)) {
    //         return true;
    //     }
    //     // guard we have a file do we recreate it
    //     if (env('REGENERATE_IMAGES', false) === true) {
    //         return true;
    //     }
    //
    //     return false;
    // }

    /*
     * check if we need to create an optimized image
     */
    public function needToCrop()
    {
        // guard
        if ($this->crop_x === null) {
            return false;
        }
        if ($this->crop_y === null) {
            return false;
        }
        // guard
        if (!$this->crop_width > 0) {
            return false;
        }
        // guard
        if (!$this->crop_height > 0) {
            return false;
        }

        return true;
    }

    /*
     *
     */
    public function setStorageDisc($storage_disc)
    {
        $this->storage_disk = Storage::disk($storage_disc);
        $this->override_storage_disk = $storage_disc;

        return $this;
    }

    /*
     *
     */
    public function setCacheDisc($cache_disc)
    {
        $this->cache_disk = Storage::disk($cache_disc);
        $this->override_cache_disk = $cache_disc;

        return $this;
    }
}
