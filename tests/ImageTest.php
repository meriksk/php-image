<?php

use PHPImage\ImageFactory;
use PHPImage\Image;


class ImageTest extends ImageTestCase
{

	public function testGetInstance()
    {
		try {

			// gd
			$im = Image::getInstance(ImageFactory::LIB_GD);
			$this->assertInstanceOf('PHPImage\Image', $im);
			$this->assertEquals('PHPImage\ImageGd', get_class($im));

			// imagick
			if (extension_loaded('Imagick')) {
				$im = Image::getInstance(ImageFactory::LIB_IMAGICK);
				$this->assertInstanceOf('PHPImage\Image', $im);
				$this->assertEquals('PHPImage\ImageImagick', get_class($im));
			}

		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testLoad()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertInstanceOf('PHPImage\Image', $im);
		$this->assertEquals('PHPImage\Image' . static::$lib, get_class($im));
	}

	public function testLoadString()
	{
		$this->markTestIncomplete();
	}

	public function testLoadBase64()
	{
		$this->markTestIncomplete();
	}

	public function testCreate()
	{
		$im = Image::create(800, 600, '#FF0000', static::$lib);

		// image
		$this->assertInstanceOf('PHPImage\Image', $im);
		$this->assertEquals('PHPImage\Image' . static::$lib, get_class($im));
		$this->assertTrue(testIsResource($im->getResource()));
		$this->assertEquals([800, 600], $im->getDimensions());

		// image original
		$orig = $im->getImageOriginal();
		$this->assertInstanceOf('PHPImage\Image', $orig);
		$this->assertEquals('PHPImage\Image' . static::$lib, get_class($orig));
		$this->assertTrue(testIsResource($orig->getResource()));
		$this->assertEquals([800, 600], $orig->getDimensions());
	}

	public function testRevert()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);

		// fresh image
		$info = $im->getInfo();

		// image modifications
		$im->thumbnail(300, 100);
		$this->assertNotEquals($info, $im->getInfo());

		// revert
		$im->revert();
		$this->assertEquals($info, $im->getInfo());
	}

	public function testDestroy()
	{
		$im = Image::create(800, 600, '#FF0000', static::$lib);
		$im->destroy();

		// properties
		$this->assertNull($im->getPath());
		$this->assertEquals(0, $im->getWidth());
		$this->assertEquals(0, $im->getHeight());
		$this->assertNull($im->getExtension());
		$this->assertNull($im->getDateCreated());

		// resources
		$this->assertNull($im->getResource());
		$this->assertNull($im->getImageOriginalResource());
	}

	public function testGetImage()
	{
		// blank image
		$blankImage = Image::create(800, 600, '#FF0000', static::$lib);
		$this->assertResource($blankImage->getResource());
		$blankImage->destroy();

		// existing image
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertResource($im->getResource());
		$im->destroy();
	}

	public function testGetImageOriginal()
	{
		// blank image
		$blankImage = Image::create(800, 600, '#FF0000', static::$lib);
		$this->assertResource($blankImage->getImageOriginalResource());
		$blankImage->destroy();

		// existing image
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertResource($im->getImageOriginalResource());
		$im->destroy();
	}

	public function testGetInfo()
	{
		$path = self::$testImageLandscape;
		$im = Image::load($path, static::$lib);
		$info = $im->getInfo();

		$this->assertNotEmpty($info);
		$this->assertEquals($path, $info['path']);
		$this->assertEquals(basename($path), $info['filename']);
		$this->assertEquals(1920, $info['width']);
		$this->assertEquals(1280, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mimeType']);
		//$this->assertEquals(time(), $info['dateCreated']);
	}

	public function testGetPath()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertEquals(self::$testImageLandscape, $im->getPath());
	}

	public function testGetWidth()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertEquals(1920, $im->getWidth());
	}

	public function testGetHeight()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertEquals(1280, $im->getHeight());
	}

	public function testGetExtension()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);
		$this->assertEquals('jpg', $im->getExtension());
	}

	public function testGetDateCreated()
	{
		$this->markTestIncomplete();
	}

	public function testRefresh()
	{
		$this->markTestIncomplete();
	}

	public function testPing()
	{
		$path = self::$testImageLandscapeExif;
		$im = Image::getInstance(static::$lib);
		$info = $im->ping($path);
		$im->destroy();

		$this->assertNotEmpty($info);
		$this->assertEquals($path, $info['path']);
		$this->assertEquals(basename($path), $info['filename']);
		$this->assertEquals(1920, $info['width']);
		$this->assertEquals(1280, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mimeType']);
		$this->assertEquals(1444401939, $info['dateCreated']);
	}

	public function testReadMetaData()
	{
		$path = self::$testImageLandscapeExif;
		$im = Image::load($path, static::$lib);
		$info = $im->readMetaData();
		$im->destroy();

		$this->assertNotEmpty($info);
		$this->assertEquals($path, $info['path']);
		$this->assertEquals(basename($path), $info['filename']);
		$this->assertEquals(1920, $info['width']);
		$this->assertEquals(1280, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mimeType']);
		$this->assertEquals(1444401939, $info['dateCreated']);
	}

	public function testSave()
	{
		$im = Image::load(self::$testImageLandscape, static::$lib);

		$tmpPath = $this->getTmpPath('testSave');
		$im->save($tmpPath);
		$this->assertFileExists($tmpPath);

		// load image
		$info = $im->ping($tmpPath);
		$this->assertEquals(1920, $info['width']);
		$this->assertEquals(1280, $info['height']);
		$this->assertEquals('image/jpeg', $info['mimeType']);
		$im->destroy();
	}

	public function testGetOrientation()
	{
		$path = self::$testImageLandscapeExif;
		$im = Image::load($path, static::$lib);
		$this->assertEquals('landscape', $im->getOrientation());
	}

	public function testFetExtensionFromPath()
	{
		$im = Image::getInstance(static::$lib);

		$this->assertEquals('png', $im->getExtensionFromPath('C:\\test\\sample_image.png'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('C:\\test\\sample_image.new.jpg'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('sample_image.jpg'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('/images/sample_image.jpg'));
	}

	public function testGetExifData()
	{
		$im = Image::getInstance(static::$lib);
		$exifKey = static::$lib === ImageFactory::LIB_IMAGICK ? 'exif:DateTime' : 'DateTime';
		$exif = $im->getExifData(NULL, self::$testImageLandscapeExif);

		$this->assertNotEmpty($exif);
		$this->assertTrue(is_array($exif));
		$this->assertArrayHasKey($exifKey, $exif);
	}

	public function testGetExifProperty()
	{
		$im = Image::load(self::$testImageLandscapeExif, static::$lib);
		$time = $im->getExifProperty('ExposureTime', self::$testImageLandscapeExif);
		$this->assertEquals($time, '1/50');
	}

	public function testUpscaleCheck()
	{
		// working image
		$im = Image::load(self::$testImageLandscape, static::$lib);

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
	}

	public function testFitToWidth()
	{
		// working image
		$im = Image::load(self::$testImageLandscape, static::$lib);

		// resize
		$im->fitToWidth(500);
		$this->assertEquals([500, 333], $im->getDimensions());

		// save + test
		$tmpPath = $this->getTmpPath('testFitToWidth');
		$im->save($tmpPath, 100);
		$info = $im->ping($tmpPath);
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
	}

	public function testFitToHeight()
	{
		// working image
		$im = Image::load(self::$testImageLandscape, static::$lib);

		// resize
		$im->fitToHeight(333);
		$this->assertEquals([500, 333], $im->getDimensions());

		// save + test
		$tmpPath = $this->getTmpPath('testFitToHeight');
		$im->save($tmpPath, 100);
		$info = $im->ping($tmpPath);
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
	}

	public function testResize()
	{
		// working image
		$im = Image::load(self::$testImageLandscape, static::$lib);

		// test 1
		$size = [500, 500];
			$im->resize($size);
			$this->assertEquals($size, $im->getDimensions());
			$tmpPath = $this->getTmpPath('testResize_1');
			$im->save($tmpPath, 100);
			$info = $im->ping($tmpPath);
			$this->assertEquals(500, $info['width']);
			$this->assertEquals(500, $info['height']);

		// test 2
		$size = [500, 50];
			$im->resize($size);
			$this->assertEquals($size, $im->getDimensions());
			$tmpPath = $this->getTmpPath('testResize_2');
			$im->save($tmpPath, 100);
			$info = $im->ping($tmpPath);
			$this->assertEquals(500, $info['width']);
			$this->assertEquals(50, $info['height']);
	}

	public function testBestFit()
	{
		// landscape image
		$im = Image::load(self::$testImageLandscape, static::$lib);

			// resize
			$im->bestFit(500);
			$this->assertEquals([500, 333], $im->getDimensions());

			$tmpPath = $this->getTmpPath('testBestFit');
			$im->save($tmpPath, 100);
			$info = $im->ping($tmpPath);
			$this->assertEquals(500, $info['width']);
			$this->assertEquals(333, $info['height']);
			$im->destroy();

		// portrait image
		$im = Image::load(self::$testImagePortrait, static::$lib);

			// resize
			$im->bestFit(500);
			$this->assertEquals([333, 500], $im->getDimensions());

			$tmpPath = $this->getTmpPath('testBestFit');
			$im->save($tmpPath, 100);
			$info = $im->ping($tmpPath);
			$this->assertEquals(333, $info['width']);
			$this->assertEquals(500, $info['height']);
			$im->destroy();
	}

	public function testThumbnail()
	{

		// landscape (1920x1280)
		$im = Image::load(self::$testImageLandscape, static::$lib);

			$dim = [2000, 2000];
				$im->thumbnail($dim);
				$this->assertEquals([1920, 1280], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1920, $info['width']);
				$this->assertEquals(1280, $info['height']);

			$dim = [400, 400];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [800, 600];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(800, $info['width']);
				$this->assertEquals(600, $info['height']);

			$dim = [600, 800];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(600, $info['width']);
				$this->assertEquals(800, $info['height']);

			$dim = [1000, 300];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(300, $info['height']);

			$dim = [300, 1000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(300, $info['width']);
				$this->assertEquals(1000, $info['height']);

			$dim = [2000, 1000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1920, 1000], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1920, $info['width']);
				$this->assertEquals(1000, $info['height']);

			$dim = [1000, 2000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1000, 1280], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(1280, $info['height']);

			$dim = [1000, 3000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1000, 1280], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(1280, $info['height']);

		$im->destroy();

		// portrait (1280x1920)
		$im = Image::load(self::$testImagePortrait, static::$lib);

			$dim = [2000, 2000];
				$im->thumbnail($dim);
				$this->assertEquals([1280, 1920], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1280, $info['width']);
				$this->assertEquals(1920, $info['height']);

			$dim = [400, 400];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [800, 600];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(800, $info['width']);
				$this->assertEquals(600, $info['height']);

			$dim = [600, 800];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(600, $info['width']);
				$this->assertEquals(800, $info['height']);

			$dim = [1000, 300];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(300, $info['height']);

			$dim = [300, 1000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(300, $info['width']);
				$this->assertEquals(1000, $info['height']);

			$dim = [2000, 1000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1280, 1000], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1280, $info['width']);
				$this->assertEquals(1000, $info['height']);

			$dim = [1000, 2000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1000, 1920], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(1920, $info['height']);

			$dim = [1000, 3000];
				$im->revert()->thumbnail($dim);
				$this->assertEquals([1000, 1920], $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(1000, $info['width']);
				$this->assertEquals(1920, $info['height']);

		$im->destroy();

		// landscape (1920x1280) - shrink enabled
		$im = Image::load(self::$testImageLandscape, static::$lib);

			$dim = [400, 400];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [500, 400];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(500, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [400, 500];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(500, $info['height']);

			$dim = [2000, 2000];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(2000, $info['width']);
				$this->assertEquals(2000, $info['height']);

		$im->destroy();

		// portrait (1280x1920) - shrink enabled
		$im = Image::load(self::$testImagePortrait, static::$lib);

			$dim = [400, 400];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [500, 400];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(500, $info['width']);
				$this->assertEquals(400, $info['height']);

			$dim = [400, 500];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(400, $info['width']);
				$this->assertEquals(500, $info['height']);

			$dim = [2000, 2000];
				$im->revert()->thumbnail($dim, true);
				$this->assertEquals($dim, $im->getDimensions());
				$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true));
				$im->save($tmpPath, 100);
				$info = $im->ping($tmpPath);
				$this->assertEquals(2000, $info['width']);
				$this->assertEquals(2000, $info['height']);

		$im->destroy();

	}

}