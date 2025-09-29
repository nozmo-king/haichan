<?php

namespace App\Helpers;

use Intervention\Image\Laravel\Facades\Image;

class ImageHelper
{
    public static function applyDither($imagePath, $outputPath = null)
    {
        if (! $outputPath) {
            $outputPath = $imagePath;
        }

        try {
            // Load the image
            $image = Image::make($imagePath);

            // Get image dimensions
            $width = $image->width();
            $height = $image->height();

            // Convert to resource for pixel manipulation
            $canvas = imagecreatetruecolor($width, $height);

            // Convert original image to resource
            $temp = tempnam(sys_get_temp_dir(), 'dither_');
            $image->save($temp, 100, 'jpg');
            $source = imagecreatefromjpeg($temp);

            // Floyd-Steinberg dithering algorithm
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    // Get current pixel
                    $rgb = imagecolorat($source, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Convert to grayscale
                    $gray = ($r + $g + $b) / 3;

                    // Quantize (threshold at 128)
                    $newGray = $gray < 128 ? 0 : 255;
                    $error = $gray - $newGray;

                    // Set pixel
                    $color = imagecolorallocate($canvas, $newGray, $newGray, $newGray);
                    imagesetpixel($canvas, $x, $y, $color);

                    // Distribute error to neighboring pixels (Floyd-Steinberg)
                    if ($x < $width - 1) {
                        self::addError($source, $x + 1, $y, $error * 7 / 16, $width, $height);
                    }
                    if ($y < $height - 1) {
                        if ($x > 0) {
                            self::addError($source, $x - 1, $y + 1, $error * 3 / 16, $width, $height);
                        }
                        self::addError($source, $x, $y + 1, $error * 5 / 16, $width, $height);
                        if ($x < $width - 1) {
                            self::addError($source, $x + 1, $y + 1, $error * 1 / 16, $width, $height);
                        }
                    }
                }
            }

            // Save dithered image
            imagejpeg($canvas, $outputPath, 85);

            // Clean up
            imagedestroy($canvas);
            imagedestroy($source);
            unlink($temp);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    private static function addError($image, $x, $y, $error, $width, $height)
    {
        if ($x >= 0 && $x < $width && $y >= 0 && $y < $height) {
            $rgb = imagecolorat($image, $x, $y);
            $r = min(255, max(0, (($rgb >> 16) & 0xFF) + $error));
            $g = min(255, max(0, (($rgb >> 8) & 0xFF) + $error));
            $b = min(255, max(0, ($rgb & 0xFF) + $error));

            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $y, $color);
        }
    }
}
