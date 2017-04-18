<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use ImageManipulator;
use Storage;

class ServeResizedImageController extends Controller
{
    const PRODUCT_IMG_PATH = '/images/product/';
    const PRODUCT_SIMG_PATH = '/images/product/resize/small/';
    const PRODUCT_MIMG_PATH = '/images/product/resize/medium/';
    const PRODUCT_LIMG_PATH = '/images/product/resize/large/';

    /**
     * Serve the appropriate image on the client
     *
     * @param   String      $size
     * @param   String      $filename
     * @return  Response
     */
    public function serveAppropriateImage($size, $filename)
    {
        $absoluteFilePath = public_path() . self::PRODUCT_IMG_PATH . $filename;

        // Check if image of queried size is available
        // If not, then find the next image
        // of size closest to the
        // queried one
        switch ($size) {
            case 'small':
                $sFilePath = self::PRODUCT_SIMG_PATH . $filename;

                if(Storage::disk('public')->exists($sFilePath)){
                    $sAbsoluteFilePath = public_path() . $sFilePath;
                    $smallImage = ImageManipulator::make($sAbsoluteFilePath);

                    return $smallImage->response();
                }
                else return $this->findNextAppropriateImage('medium', $filename);

            case 'medium':
                $mFilePath = self::PRODUCT_MIMG_PATH . $filename;

                if(Storage::disk('public')->exists($mFilePath)){
                    $mAbsoluteFilePath = public_path() . $mFilePath;
                    $mediumImage = ImageManipulator::make($mAbsoluteFilePath);

                    return $mediumImage->response();
                }
                else return $this->findNextAppropriateImage('large', $filename);

            case 'large':
                $lFilePath = self::PRODUCT_LIMG_PATH . $filename;

                if(Storage::disk('public')->exists($lFilePath)){
                    $lAbsoluteFilePath = public_path() . $lFilePath;
                    $largeImage = ImageManipulator::make($lAbsoluteFilePath);

                    return $largeImage->response();
                }
                else return $this->findNextAppropriateImage('default', $filename);

            default:
                $defaultImage = ImageManipulator::make($absoluteFilePath);

                return $defaultImage->response();
        }
    }

    /**
     * Find the next closest image size if
     * queried image size is not found
     *
     * @param   String      $startingSize
     * @param   String      $filename
     * @return  Response
     */
    private function findNextAppropriateImage($startingSize, $filename)
    {
        $indexOfSize = [
            'medium'  => 0,
            'large'   => 1,
            'default' => 99
        ];

        $filePaths = [
            self::PRODUCT_MIMG_PATH . $filename,
            self::PRODUCT_LIMG_PATH . $filename
        ];

        // Start searching in medium and large images
        for ($i = $indexOfSize[$startingSize]; $i < count($filePaths); $i++) {

            if(Storage::disk('public')->exists($filePaths[$i])){
                $absoluteFilePath = public_path() . $filePaths[$i];
                $image = ImageManipulator::make($absoluteFilePath);

                return $image->response();
            }
        }

        // If no matches then just return the default image
        $absoluteFilePath = public_path() . self::PRODUCT_IMG_PATH . $filename;
        $image = ImageManipulator::make($absoluteFilePath);

        return $image->response();

    }
}
