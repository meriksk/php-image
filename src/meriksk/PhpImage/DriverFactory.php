<?php

namespace meriksk\PhpImage;

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
	public static function get($driver = NULL)
	{
		switch ($driver) {
			case Image::DRIVER_IMAGICK:
				if (extension_loaded('Imagick')) { 
					return new driver\ImageImagick();
				} else {
					throw new Exception('PHP extension Imagick is not installed.'); 
				}
				break;
			case Image::DRIVER_GD:
			default: 
				if (extension_loaded('gd')) { 
					return new driver\ImageGd();
				} else {
					throw new Exception('PHP extension GD is not installed.'); 
				}
				break;
		}
	}

}
