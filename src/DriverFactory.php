<?php

namespace meriksk\PhpImage;

use Exception;

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
	public static function get($driver = null)
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
