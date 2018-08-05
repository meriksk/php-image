<?php

use PHPImage\ImageFactory;

class ImageFactoryTest extends ImageTestCase
{

	public function testGet()
    {
		try {

			// GD
			$instance = ImageFactory::get(ImageFactory::LIB_GD);
			$this->assertInstanceOf('PHPImage\Image', $instance);
			$this->assertEquals('PHPImage\ImageGd', get_class($instance));

			// Imagick
			if (extension_loaded('Imagick')) {
				$instance = ImageFactory::get(ImageFactory::LIB_IMAGICK);
				$this->assertInstanceOf('PHPImage\Image', $instance);
				$this->assertEquals('PHPImage\ImageImagick', get_class($instance));
			}
			
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}

	}

}
