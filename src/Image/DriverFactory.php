<?php

namespace meriksk\Image;

use Exception;


// PHP
// getimagesizefromstring — Get the size of an image from a string
// (PHP 5 >= 5.4.0)
if (!function_exists('getimagesizefromstring')) {
	function getimagesizefromstring($data, &$imageinfo = []) {
		$uri = 'data://application/octet-stream;base64,' . base64_encode($data);
		return getimagesize($uri, $imageinfo);
	}
}

/**
 * DriverFactory class file.
 */
class DriverFactory
{

	/**
	 * Get Image instance
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function get($lib = NULL)
	{
		// check requirements
		if (!extension_loaded(Image::DRIVER_GD) && !extension_loaded(Image::DRIVER_IMAGICK)) {
			throw new Exception('Required extensions (GD or Imagick) are not installed or turned on.');
		}
		
		// auto-detect available library
		if ($lib === NULL) {
			if (extension_loaded('Imagick')) {
				return new driver\ImageImagick();
			} else {
				return new driver\ImageGd();
			}
		} else {
			if ($lib === Image::DRIVER_IMAGICK) {
				if (extension_loaded('Imagick')) {
					return new driver\ImageImagick();
				} else {
					throw new Exception('PHP extension Imagick is not installed.');
				}
			} else {
				if (extension_loaded('gd')) {
					return new driver\ImageGd();
				} else {
					throw new Exception('PHP extension GD is not installed.');
				}
			}
		}
	}

}
