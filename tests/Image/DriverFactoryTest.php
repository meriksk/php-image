<?php

namespace meriksk\PhpImage\Tests\Image;

use meriksk\PhpImage\Tests\BaseTestCase;
use meriksk\PhpImage\Image;
use meriksk\PhpImage\DriverFactory;


class DriverFactoryTest extends BaseTestCase
{
	public function testGet()
    {
		try {

			// GD
			$instance = DriverFactory::get(Image::DRIVER_GD);
			$this->assertEquals('meriksk\PhpImage\driver\ImageGd', get_class($instance));

			// Imagick
			if (extension_loaded('Imagick')) {
				$instance = DriverFactory::get(Image::DRIVER_IMAGICK);
				$this->assertEquals('meriksk\PhpImage\driver\ImageImagick', get_class($instance));
			}
			
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
	}
}
