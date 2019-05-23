<?php

use meriksk\Image\ImageFactory;

class ImageFactoryTest extends ImageTestCase
{

	public function testGet()
    {
		try {

			// GD
			$instance = ImageFactory::get(ImageFactory::LIB_GD);
			$this->assertInstanceOf('meriksk\Image\Image', $instance);
			$this->assertEquals('meriksk\Image\drivers\ImageGd', get_class($instance));

			// Imagick
			if (extension_loaded('Imagick')) {
				$instance = ImageFactory::get(ImageFactory::LIB_IMAGICK);
				$this->assertInstanceOf('meriksk\Image\Image', $instance);
				$this->assertEquals('meriksk\Image\drivers\ImageImagick', get_class($instance));
			}
			
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}

	}

}
