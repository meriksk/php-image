<?php

use merik\Image\ImageFactory;

class ImageFactoryTest extends ImageTestCase
{

	public function testGet()
    {
		try {

			// GD
			$instance = ImageFactory::get(ImageFactory::LIB_GD);
			$this->assertInstanceOf('merik\Image\Image', $instance);
			$this->assertEquals('merik\Image\ImageGd', get_class($instance));

			// Imagick
			if (extension_loaded('Imagick')) {
				$instance = ImageFactory::get(ImageFactory::LIB_IMAGICK);
				$this->assertInstanceOf('merik\Image\Image', $instance);
				$this->assertEquals('merik\Image\ImageImagick', get_class($instance));
			}
			
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}

	}

}
