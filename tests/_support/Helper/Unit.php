<?php

namespace Helper;

use \meriksk\PhpImage\Image;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{

	/**
	 * Initialize an image driver
	 * @param string $driver
	 * @return \meriksk\PhpImage\BaseImage
	 */
	public function initDriver($driver = Image::DRIVER_GD)
	{
		$image = new Image(null, $driver);
		return $image->getDriver();
	}
	
}
