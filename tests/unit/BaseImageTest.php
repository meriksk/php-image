<?php

namespace tests\unit;

use tests\ImageTestCase;
use meriksk\PhpImage\DriverFactory;
use meriksk\PhpImage\Image;


class BaseImageCest extends ImageTestCase
{
	
	public function testCheckMimeType()
	{
		$image = new Image();
		
		// test protected method
		$reflection = new \ReflectionClass(get_class($image->driver));
		$method = $reflection->getMethod('checkMimeType');
		$method->setAccessible(true);
		
		// test
		$mime = $method->invokeArgs($image->driver, ['png']);
		$this->assertEquals('image/png', $mime);
		
		// test
		$mime = $method->invokeArgs($image->driver, [IMAGETYPE_PNG]);
		$this->assertEquals('image/png', $mime);
		
		// test
		$mime = $method->invokeArgs($image->driver, ['image/png']);
		$this->assertEquals('image/png', $mime);
		
		// test
		$this->expectException(\Exception::class);
		$mime = $method->invokeArgs($image->driver, [null]);
	}
	
	public function testUpscaleCheck()
	{
		$this->markTestIncomplete('@todo');
		
		/*

		// working image
		$im = Image::load(static::$imageLandscape, static::$lib);

		// working image has same dimensions than desired
		// 1920x1280  ->  max: 1920x1280
		$checked = $im->upscaleCheck(1920, 1280);
		$this->assertEquals([1920, 1280], $checked);

		// working image has smaller dimensions than desired
		// 1920x1280  ->  max: 3000x3000
		$checked = $im->upscaleCheck(3000, 3000);
		$this->assertEquals([1920, 1280], $checked);

		// working image has larger dimensions than desired
		// 1920x1280  ->  max: 800x800
		$checked = $im->upscaleCheck(800, 800);
		$this->assertEquals([800, 800], $checked);
		 
		*/
	}
	
}