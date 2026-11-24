<?php

class ImageCompressor {

    public static function compress($source, $destination, $quality = 70) {
        $info = getimagesize($source);

        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                imagejpeg($image, $destination, $quality);
                break;

            case 'image/png':
                $image = imagecreatefrompng($source);
                imagepng($image, $destination, 6);
                break;

            case 'image/webp':
                $image = imagecreatefromwebp($source);
                imagewebp($image, $destination, $quality);
                break;

            default:
                return false;
        }

        imagedestroy($image);
        return true;
    }
}
