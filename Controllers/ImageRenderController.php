<?php
// version 11

namespace App\Http\Controllers;

use App\Classes\ImageFactory\ImageFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request as RequestFacade;

class ImageRenderController extends Controller
{
    public function __construct(Request $request)
    {
//        if (!$request->hasValidSignature()) {
//            abort(401);
//        }
    }

    /*
     * width and height are max values, it resize to the smallest edge
     * if you the best possible image with both height and with use "fit"
     *
     * parameters:
     * default = crop / keep-aspect-ratio / no-upscale / optimized
     *
     * f = change crop to fit (use fit if you want to make sure the width and height are the given value)
     * s = change keep-aspect-ratio to strach
     * u = change no-upscale to upscale
     * r = change optimized to not-optimized
     */
    public function image($max_width, $max_height, $param = 0, $public_path)
    {
        //
        $timestamp = RequestFacade::get('timestamp', null);
        $crop_x = RequestFacade::get('crop_x', null);
        $crop_y = RequestFacade::get('crop_y', null);
        $crop_width = RequestFacade::get('crop_width', null);
        $crop_height = RequestFacade::get('crop_height', null);
        $storage = RequestFacade::get('storage', null);
        $cache = RequestFacade::get('cache', null);

        // get image url
        $image_factory = ImageFactory::create($public_path, $max_width, $max_height, $param)
            ->setCrop($crop_x, $crop_y, $crop_width, $crop_height)
            ->setTimestamp($timestamp);

        // if we use another storage thing
        if ($storage) {
            $image_factory->setStorageDisk($storage);
        }

        // if we use another storage thing
        if ($cache) {
            $image_factory->setCacheDisk($cache);
        }

        // go on
        $image_factory->generateOptimizedImagePath();

        // create if not exists or need to remake
        $image_factory->createImage();

        //
        $url_to_file = $image_factory->getCachedUrl();

        // $cache_key = ImageFactory::getCacheKey($this->getTable(), $id);

        return redirect($url_to_file);
    }
}
