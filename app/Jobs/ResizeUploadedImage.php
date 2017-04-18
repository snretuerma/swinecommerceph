<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use ImageManipulator;
use Storage;

class ResizeUploadedImage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    const PRODUCT_IMG_PATH = '/images/product/';
    const PRODUCT_SIMG_PATH = '/images/product/resize/small/';
    const PRODUCT_MIMG_PATH = '/images/product/resize/medium/';
    const PRODUCT_LIMG_PATH = '/images/product/resize/large/';

    protected $filename;

    /**
     * Create a new job instance.
     *
     * @param  String   filename
     * @return void
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Resize image according to size
     * - L thumbnail [<> x 410]
     * - M thumbnail [<> x 270]
     * - S thumbnail [<> x 75]
     *
     * @return void
     */
    public function handle()
    {
        $absoluteFilePath = public_path() . self::PRODUCT_IMG_PATH . $this->filename;

        $image = ImageManipulator::make($absoluteFilePath);

        // Do not save resized image if original height is less
        // than the supposed to be rescaled image
        while(true){

            if($image->height() > 75){
                $smallImage = ImageManipulator::make($absoluteFilePath)->heighten(75, function($constraint){
                    $constraint->upsize();
                });

                // Make directory if it does not exist
                if(!Storage::disk('public')->exists(self::PRODUCT_SIMG_PATH)){
                    Storage::disk('public')->makeDirectory(self::PRODUCT_SIMG_PATH);
                }

                $smallImage->save(public_path() . self::PRODUCT_SIMG_PATH . $this->filename);
            }
            else break;

            if($image->height() > 270){
                $mediumImage =  ImageManipulator::make($absoluteFilePath)->heighten(270, function($constraint){
                    $constraint->upsize();
                });

                // Make directory if it does not exist
                if(!Storage::disk('public')->exists(self::PRODUCT_MIMG_PATH)){
                    Storage::disk('public')->makeDirectory(self::PRODUCT_MIMG_PATH);
                }

                $mediumImage->save(public_path() . self::PRODUCT_MIMG_PATH . $this->filename, 80);
            }
            else break;

            if($image->height() > 410){
                $largeImage =  ImageManipulator::make($absoluteFilePath)->heighten(410, function($constraint){
                    $constraint->upsize();
                });

                // Make directory if it does not exist
                if(!Storage::disk('public')->exists(self::PRODUCT_LIMG_PATH)){
                    Storage::disk('public')->makeDirectory(self::PRODUCT_LIMG_PATH);
                }

                $largeImage->save(public_path() . self::PRODUCT_LIMG_PATH . $this->filename, 80);
            }

            break;
        }

    }
}
