<?php

namespace merik\Image;

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
 * CImageFactory class.
 * @return CImage library
 */
class ImageFactory
{
	
	/**
	 * @var string
	 */
	const LIB_GD = 'Gd';
	const LIB_IMAGICK = 'Imagick';
	
	
	/**
	 * Get Image instance
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function get($lib = NULL)
	{
		
		// check requirements
		if (!extension_loaded('gd') && !extension_loaded('Imagick')) {
			throw new Exception('Required extensions (GD or Imagick) are not installed or turned on.');
		}
		
		// auto-detect available library
		if ($lib === NULL) {
			if (extension_loaded('Imagick')) {
				return new ImageImagick();
			} else {
				return new ImageGd();
			}
		} else {
			if ($lib === self::LIB_IMAGICK) {
				if (extension_loaded('Imagick')) {
					return new ImageImagick();
				} else {
					throw new Exception('PHP extension Imagick not installed.');
				}
			} else {
				if (extension_loaded('gd')) {
					return new ImageGd();
				} else {
					throw new Exception('PHP extension GD not installed.');
				}
			}
		}
	}

}
