<?php

use meriksk\PhpImage\ImageFactory;

class ImageFactoryTest extends ImageTestCase
{

	public function testGet()
    {
		try {

			// GD
			$instance = ImageFactory::get(ImageFactory::LIB_GD);
			$this->assertInstanceOf('meriksk\PhpImage\Image', $instance);
			$this->assertEquals('meriksk\PhpImage\drivers\ImageGd', get_class($instance));

			// Imagick
			if (extension_loaded('Imagick')) {
				$instance = ImageFactory::get(ImageFactory::LIB_IMAGICK);
				$this->assertInstanceOf('meriksk\PhpImage\Image', $instance);
				$this->assertEquals('meriksk\PhpImage\drivers\ImageImagick', get_class($instance));
			}
			
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}

	}

}
