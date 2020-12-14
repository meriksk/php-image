<?php

namespace tests\unit;

use tests\ImageTestCase;
use meriksk\PhpImage\DriverFactory;
use meriksk\PhpImage\Image;



class ImageTest extends ImageTestCase
{

	public function testConstruct()
	{
		// path
		$image = new Image(self::$imageLandscape, static::$lib);
		$this->assertImage($image);

		// data url
		$image = new Image('data:image/gif;base64,R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
		$this->assertImage($image);

		// string (invalid filename - must throw an exception)
		$this->expectException(\Exception::class);
		$image = new Image('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
	}

	public function testGetInstance()
    {
		$image = Image::getInstance(static::$lib);

		$this->assertImage($image);
	}

	public function testLoad()
	{
		$image = Image::load(self::$imageLandscape, static::$lib);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testLoadFromFile()
	{
		$image = new Image(null, static::$lib);
		$image->loadFromFile(self::$imageLandscape);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testFromFile()
	{
		$image = Image::fromFile(self::$imageLandscape, static::$lib);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testLoadFromString()
	{
		// valid image data
		$imageData = file_get_contents(self::$imageLandscape);
		$image = new Image(null, static::$lib);
		$image->loadFromString($imageData);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);

		// invalid image data
		//$this->expectException(\Exception::class);
		//$image = new Image(null, static::$lib);
		//$image->loadFromString('GIF89a☺☺�♦☻♦���!�♦☺☺,☺☺☻☻D☺;', static::$lib);
	}

	public function testFromString()
	{
		$imageData = file_get_contents(self::$imageLandscape);
		$image = Image::fromString($imageData, static::$lib);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testLoadFromBase64()
	{
		$image = new Image(null, static::$lib);
		$image->loadFromBase64('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testFromBase64()
	{
		$image = Image::fromBase64('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==', static::$lib);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\PhpImage\driver\Image'. ucfirst(static::$lib), $image->driver);
	}

	public function testPing()
	{
		/*
		// path
		$image = new Image(self::$imageLandscape, static::$lib);
		$info = $image->ping();

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(self::$imageLandscape, $info['path']);
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);

		// string
		$image = Image::fromString(file_get_contents(self::$imageLandscape));
		$info = $image->ping();
		$image->destroy();

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
		*/

		// remote image
		// exif
		$image = new Image(self::$imageRemoteExif, static::$lib);
		$info = $image->ping();

		$this->assertEquals(self::$imageRemoteExif, $info['path']);
		$this->assertEquals(533, $info['width']);
		$this->assertEquals(800, $info['height']);
		$this->assertEquals('portrait', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);
	}

	public function testPingImage()
	{
		$info = Image::pingImage(self::$imageLandscape, static::$lib);

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(self::$imageLandscape, $info['path']);
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);
	}

	public function testGetInfo()
	{
		/*
		$image = new Image(self::$imageLandscape, static::$lib);
		$info = $image->getInfo();

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(self::$imageLandscape, $info['path']);
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);

		// portrait
		$image->loadFromFile(self::$imagePortrait);
		$info = $image->getInfo();

		$this->assertEquals(533, $info['width']);
		$this->assertEquals(800, $info['height']);
		$this->assertEquals('portrait', $info['orientation']);
		*/

		// remote image + exif
		$image = new Image(self::$imageExifGps, static::$lib);
		$info = $image->getInfo(true);

		$this->assertArrayHasKey('exif', $info);
	}

	public function testDestroy()
	{
		$image = new Image(self::$imageLandscape, static::$lib);
		$image->destroy();
		$info = $image->getInfo();

		$this->assertIsArray($info);
		$this->assertEmpty($info);
		$this->assertNull($image->getResource());
		$this->assertNull($image->getPath());
		$this->assertNull($image->getWidth());
		$this->assertNull($image->getHeight());
	}

	public function testGetResource()
	{
		$image = new Image(self::$imageLandscape, static::$lib);
		$resource = $image->getResource();

		$this->assertResource($resource);
	}

	public function testGetPath()
	{
		$image = new Image(self::$imageLandscape, static::$lib);
		$path = $image->getPath();

		$this->assertEquals(self::$imageLandscape, $path);
	}

	public function testGetExtensionFromPath()
	{
		$image = new Image(self::$imageLandscape, static::$lib);
		$path = $image->getExtensionFromPath();

		$this->assertEquals('jpg', $path);
	}

	public function testGetDimensions()
	{
		// valid image
		$image = new Image(static::$imageLandscape, static::$lib);
		$dimensions = $image->getDimensions();

		$this->assertIsArray($dimensions);
		$this->assertEquals([500, 333], $dimensions);

		// invalid image
		$this->expectException(\Exception::class);
		$image = new Image('invalid', static::$lib);
		$dimensions = $image->getDimensions();


		$this->assertFalse($dimensions);
	}

	public function testGetWidth()
	{
		// valid image
		$image = new Image(static::$imageLandscape, static::$lib);
		$w = $image->getWidth();

		$this->assertEquals(500, $w);

		// invalid image
		$this->expectException(\Exception::class);
		$image = new Image('invalid', static::$lib);
		$w = $image->getWidth();

		$this->assertNull($w);
	}

	public function testGetHeight()
	{
		$image = new Image(static::$imageLandscape, static::$lib);
		$h = $image->getHeight();

		$this->assertEquals(333, $h);
	}

	public function testGetMimeType()
	{
		$image = new Image(static::$imageLandscape, static::$lib);
		$mimeType = $image->getMimeType();

		$this->assertEquals('image/jpeg', $mimeType);
	}

	public function testGetExtension()
	{
		$image = new Image(static::$imageLandscape, static::$lib);
		$ext1 = $image->getExtension();
		$ext2 = $image->getExtension(false);
		$ext3 = $image->getExtension(true);

		$this->assertEquals('jpg', $ext1);
		$this->assertEquals('jpg', $ext2);
		$this->assertEquals('.jpg', $ext3);
	}

	public function testGetOrientation()
	{
		// test 1 (landscape)
		$image = new Image(static::$imageLandscape, static::$lib);
		$orientation = $image->getOrientation();

		$this->assertEquals(Image::ORIENTATION_LANDSCAPE, $orientation);

		// test 2 (portrait)
		$image = new Image(static::$imagePortrait, static::$lib);
		$orientation = $image->getOrientation();

		$this->assertEquals(Image::ORIENTATION_PORTRAIT, $orientation);
	}

	public function testSave()
	{
		// LOAD JPG -> SAVE JPG (valid path)
		$image = new Image(static::$imageLandscape, static::$lib);
		$path = $this->getTmpPath('test_save_1.jpg');
		$result = $image->save($path);

		$this->assertTrue($result);
		$this->assertFileExists($path);

		// LOAD JPG -> SAVE JPG (invalid path)
		$result = $image->save('/invalid_path/image.jpg');
		$this->assertFalse($result);

		// LOAD JPG -> SAVE PNG (valid path)
		$image = new Image(static::$imageLandscape, static::$lib);
		$path = $this->getTmpPath('test_save_2.png');
		$result = $image->save($path, 80, 'png');

		$this->assertTrue($result);
		$this->assertFileExists($path);

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $path);
		finfo_close($finfo);

		$this->assertEquals('image/png', $mimeType);
	}

	public function testToString()
	{
		// LOAD JPG -> ECHO PNG
		$image = new Image(static::$imageLandscape, static::$lib);
		$data = $image->toString(100, 'png');

		$this->assertIsString($data);

		// RE-CREATE THE IMAGE
		$image = Image::fromString($data);
		$info = $image->getInfo();

		$this->assertImage($image);
		$this->assertEquals('image/png', $info['mime_type']);
	}

	public function testToBase64()
	{
		// LOAD JPG -> ECHO PNG
		$image = new Image(static::$imageLandscape, static::$lib);
		$data = $image->toBase64(100, 'png');

		$this->assertIsString($data);

		// RE-CREATE THE IMAGE
		$image = Image::fromBase64($data);
		$info = $image->getInfo();

		$this->assertImage($image);
		$this->assertEquals('image/png', $info['mime_type']);
	}

	public function testToDataUri()
	{
		// LOAD JPG -> ECHO PNG
		$image = new Image(static::$imageLandscape, static::$lib);
		$data = $image->toDataUri(100, 'png');

		$this->assertIsString($data);
		$this->assertTrue(strpos($data, 'data:')===0);
	}

	public function testResize()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resize(100, 600, true);
		$path = $this->getTmpPath('resize_100x600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([100, 600], $image->getDimensions());
		$this->assertEquals(100, $info['width']);
		$this->assertEquals(600, $info['height']);

		// test 2 (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resize(100, 600, false);
		$path = $this->getTmpPath('resize_100x600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertNotEquals([100, 600], $image->getDimensions());
		$this->assertNotEquals(100, $info['width']);
		$this->assertNotEquals(600, $info['height']);

		// resize transparent images
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->setBackgroundColor(Image::COLOR_TRANSPARENT);
		$image->thumbnail(100, 100);
		$path = $this->getTmpPath('resize_transparent_100.png'); $image->save($path, 100, 'png');

		// resize transparent (50%) image
		$image->revert();
		$image->setBackgroundColor([0, 0, 255, 0.50]);
		$image->thumbnail(100, 100);
		$path = $this->getTmpPath('resize_transparent_50.png'); $image->save($path, 100, 'png');

		// resize transparent (25%) image
		$image->revert();
		$image->setBackgroundColor([0, 0, 255, 0.25]);
		$image->thumbnail(100, 100);
		$path = $this->getTmpPath('resize_transparent_25.png'); $image->save($path, 100, 'png');
		$image->destroy();
	}

	public function testResizeToWidth()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToWidth(300, true);
		$path = $this->getTmpPath('resizeToWidth_300.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([300, 200], $image->getDimensions());
		$this->assertEquals(300, $info['width']);
		$this->assertEquals(200, $info['height']);

		// test 2 (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToWidth(1000, false);
		$path = $this->getTmpPath('resizeToWidth_1000.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([500, 333], $image->getDimensions());
	}

	public function testResizeToHeight()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToHeight(600, true);
		$path = $this->getTmpPath('resizeToHeight_600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([901, 600], $image->getDimensions());
		$this->assertEquals(901, $info['width']);
		$this->assertEquals(600, $info['height']);

		// test 2 (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToHeight(700, false);
		$path = $this->getTmpPath('resizeToHeight_700.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([500, 333], $image->getDimensions());
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
	}

	public function testResizeToShortSide()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToShortSide(600, true);
		$path = $this->getTmpPath('resizeToShortSide_600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([900, 600], $image->getDimensions());
		$this->assertEquals(900, $info['width']);
		$this->assertEquals(600, $info['height']);

		// test 2: landscape (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToShortSide(700, false);
		$path = $this->getTmpPath('resizeToShortSide_700.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([500, 333], $image->getDimensions());
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
	}

	public function testResizeToLongSide()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToLongSide(600, true);
		$path = $this->getTmpPath('resizeToLongSide_600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([600, 399], $image->getDimensions());
		$this->assertEquals(600, $info['width']);
		$this->assertEquals(399, $info['height']);

		// test 2: landscape (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToLongSide(700, false);
		$path = $this->getTmpPath('resizeToLongSide_700.jpg');
		$image->save($path);
		$info = Image::pingImage($path, static::$lib);

		$this->assertEquals([500, 333], $image->getDimensions());
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
	}

	public function testResizeToBestFit()
	{
		$imageLandscape = new Image(static::$imageLandscape, static::$lib); // 800x533
		$imagePortrait = new Image(static::$imagePortrait, static::$lib); // 533x800

		// test 1 (allowEnlarge: true)
		$imageLandscape->resizeToBestFit(700, 700, true);
		$imagePortrait->resizeToBestFit(700, 700, true);

		$this->assertEquals([700, 466], $imageLandscape->getDimensions());
		$this->assertEquals([466, 700], $imagePortrait->getDimensions());

		$imageLandscape->revert();
		$imagePortrait->revert();

		// test 2 (allowEnlarge: false)
		$imageLandscape->resizeToBestFit(700, 700, false);
		$imagePortrait->resizeToBestFit(700, 700, false);

		$this->assertEquals([800, 533], $imageLandscape->getDimensions());
		$this->assertEquals([533, 800], $imagePortrait->getDimensions());

		$imageLandscape->revert();
		$imagePortrait->revert();

		// test 3 (allowEnlarge: true)
		$imageLandscape->resizeToBestFit(400, 300, false);
		$imagePortrait->resizeToBestFit(400, 300, false);

		$this->assertEquals([400, 267], $imageLandscape->getDimensions());
		$this->assertEquals([200, 300], $imagePortrait->getDimensions());
	}

	public function testCrop()
	{
		// working image (800x533)
		$image = new Image(static::$imageLandscape, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->crop(0, 0, 200, 150, false);
		$path = $this->getTmpPath('crop_0_0_200x150.jpg'); $image->save($path);
		$this->assertEquals([200, 150], $image->getDimensions());

		// test 2 (allowEnlarge: false)
		$image->revert();
		$image->crop(0, 0, 800, 800, false);
		$path = $this->getTmpPath('crop_0_0_800x800.jpg'); $image->save($path);
		$this->assertEquals([800, 533], $image->getDimensions());

		// test 3 (allowEnlarge: true)
		$image->revert();
		$image->crop(0, 0, 850, 850, true);
		$path = $this->getTmpPath('crop_0_0_850x850.jpg'); $image->save($path);
		$this->assertEquals([850, 850], $image->getDimensions());

		// test 4 (allowEnlarge: false)
		$image->revert();
		$image->crop(400, 400, 900, 900, false);
		$path = $this->getTmpPath('crop_400_400_900x900.jpg'); $image->save($path);
		$this->assertEquals([400, 133], $image->getDimensions());
	}

	public function testCropAuto()
	{
		// working image (800x533)
		$image = new Image(static::$imageLandscape, static::$lib);

		// test
		$image->cropAuto(400, 400, Image::CROP_CENTER);
		$path = $this->getTmpPath('cropAuto_400x400_center.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_LEFT);
		$path = $this->getTmpPath('cropAuto_400x400_left.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_RIGHT);
		$path = $this->getTmpPath('cropAuto_400x400_right.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_TOP);
		$path = $this->getTmpPath('cropAuto_400x400_top.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_BOTTOM);
		$path = $this->getTmpPath('cropAuto_400x400_bottom.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_TOP_LEFT);
		$path = $this->getTmpPath('cropAuto_400x400_top_left.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_BOTTOM_LEFT);
		$path = $this->getTmpPath('cropAuto_400x400_bottom_left.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_BOTTOM_RIGHT);
		$path = $this->getTmpPath('cropAuto_400x400_bottom_right.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());

		// test
		$image->revert();
		$image->cropAuto(400, 400, Image::CROP_TOP_RIGHT);
		$path = $this->getTmpPath('cropAuto_400x400_top_right.jpg'); $image->save($path);
		$this->assertEquals([400, 400], $image->getDimensions());
	}

	public function testThumbnail()
	{
		// working image (800x533)
		$image = new Image(static::$imageLandscape, static::$lib);

		// thumbnail is smaller than original image (landscape)
		// fill: false, allowEnlarge: false
		$image->thumbnail(300, 150, false, false);
		$path = $this->getTmpPath('thumbnail_300x150.jpg'); $image->save($path);
		$this->assertEquals([300, 150], $image->getDimensions());

		// tthumbnail is smaller than original image (portrait)
		// fill: false, allowEnlarge: false
		$image->revert();
		$image->thumbnail(150, 300, false, false);
		$path = $this->getTmpPath('thumbnail_150x300.jpg'); $image->save($path);
		$this->assertEquals([150, 300], $image->getDimensions());

		// thumbnail is smaller than original image (landscape)
		// fill: true, allowEnlarge: false
		$image->revert();
		$image->thumbnail(300, 150, true, false);
		$path = $this->getTmpPath('thumbnail_fill_300x150.jpg'); $image->save($path);
		$this->assertEquals([300, 150], $image->getDimensions());

		// thumbnail is smaller than original image (landscape)
		// fill: true, allowEnlarge: false
		$image->revert();
		$image->thumbnail(150, 300, true, false);
		$path = $this->getTmpPath('thumbnail_fill_150x300.jpg'); $image->save($path);
		$this->assertEquals([150, 300], $image->getDimensions());

		// thumbnail is larger than original image (landscape)
		// fill: false, allowEnlarge: true
		$image->revert();
		$image->thumbnail(900, 300, false, true);
		$path = $this->getTmpPath('thumbnail_900x300.jpg'); $image->save($path);
		$this->assertEquals([900, 300], $image->getDimensions());

		// thumbnail is larger than original image (landscape)
		// fill: false, allowEnlarge: true
		$image->revert();
		$image->thumbnail(300, 800, false, true);
		$path = $this->getTmpPath('thumbnail_300x800.jpg'); $image->save($path);
		$this->assertEquals([300, 800], $image->getDimensions());

		// thumbnail is larger than original image (landscape)
		// fill: true, allowEnlarge: true
		$image->revert();
		$image->thumbnail(300, 800, true, true);
		$path = $this->getTmpPath('thumbnail_fill_300x800.jpg'); $image->save($path);
		$this->assertEquals([300, 800], $image->getDimensions());

		// thumbnail is larger than original image (landscape)
		// fill: true, allowEnlarge: false
		$image->revert();
		$image->thumbnail(500, 500, false);
		$path = $this->getTmpPath('thumbnail_500x500.jpg'); $image->save($path);
		$this->assertEquals([500, 500], $image->getDimensions());

		// thumbnail is larger than original image (landscape)
		// fill: true, allowEnlarge: false
		$image->revert();
		$image->thumbnail(500, 500, true);
		$path = $this->getTmpPath('thumbnail_fill_500x500.jpg'); $image->save($path);
		$this->assertEquals([500, 500], $image->getDimensions());
		$image->destroy();

		// working image (533x800)
		$image = new Image(static::$imagePortrait, static::$lib);

		// thumbnail is smaller than original image (landscape)
		// fill: false, allowEnlarge: false
		$image->thumbnail(300, 150, false, false);
		$path = $this->getTmpPath('thumbnail_portrait_300x150.jpg'); $image->save($path);
		$this->assertEquals([300, 150], $image->getDimensions());

	}
	
	public function testFlip()
	{
		// working image (610x621)
		$image = new Image(static::$imageTransparentPng, static::$lib);

		// test 1
		$image->flip(Image::FLIP_VERTICAL);
		$path = $this->getTmpPath('flip_vertical.png'); $image->save($path);
		$this->assertEquals([610, 621], $image->getDimensions());

		// test 2
		$image->revert();
		$image->flip(Image::FLIP_HORIZONTAL);
		$path = $this->getTmpPath('flip_horizontal.png'); $image->save($path);
		$this->assertEquals([610, 621], $image->getDimensions());

		// test 3
		$image->revert();
		$image->flip(Image::FLIP_BOTH);
		$path = $this->getTmpPath('flip_both.png'); $image->save($path);
		$this->assertEquals([610, 621], $image->getDimensions());
	}
	
	public function testRotate()
	{
		// jpg
		$jpg = new Image(static::$imageLandscape, static::$lib);
		$jpg->rotate(45, '#ff0000');
		$path = $this->getTmpPath('rotate_45.jpg'); $jpg->save($path);
		$jpg->destroy();

		// png
		$png = new Image(static::$imageTransparentPng, static::$lib);
		$png->rotate(45);
		$path = $this->getTmpPath('rotate_45.png'); $png->save($path);
		$png->destroy();

		// gif
		$png = new Image(static::$imageTransparentGif, static::$lib);
		$png->rotate(45, '#ff0000');
		$path = $this->getTmpPath('rotate_45.gif'); $png->save($path);
		$png->destroy();
	}

	public function testSetBackgroundColor()
	{
		// jpeg
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->setBackgroundColor('ff0000');
		$path = $this->getTmpPath('setBackgroundColor_ff0000.jpg'); $image->save($path);
		$image->destroy();

		// transparent png
		$image = new Image(static::$imageTransparentPng, static::$lib);
		$image->setBackgroundColor([255, 0, 0, 0.0]);
		$image->thumbnail(200, 100);
		$path = $this->getTmpPath('setBackgroundColor_ff0000_transparent.png'); $image->save($path, 100, 'png');
		$image->destroy();

		// transparent gif
		$image = new Image(static::$imageTransparentGif, static::$lib);
		$image->setBackgroundColor([255, 0, 0, 0.0]);
		$image->thumbnail(200, 100);
		$path = $this->getTmpPath('setBackgroundColor_ff0000_transparent.gif'); $image->save($path, 100, 'gif');
		$image->destroy();
	}

	public function testNormalizeColor()
	{
		$c = Image::normalizeColor('000');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 1.0], $c);

		$c = Image::normalizeColor('#ccc');
		$this->assertEquals(['r' => 204, 'g' => 204, 'b' => 204, 'a' => 1.0], $c);

		$c = Image::normalizeColor('00ccFF');
		$this->assertEquals(['r' => 0, 'g' => 204, 'b' => 255, 'a' => 1.0], $c);

		$c = Image::normalizeColor('#00ccFF7f');
		$this->assertEquals(['r' => 0, 'g' => 204, 'b' => 255, 'a' => 0.5], $c);

		$c = Image::normalizeColor('black');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 1.0], $c);

		$c = Image::normalizeColor([0, 0, 255]);
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1.0], $c);

		$c = Image::normalizeColor([0, 0, 255, 0.5]);
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 0.5], $c);
	}

	public function testHex2percentage()
	{
		$c = Image::hex2percentage('00');
		$this->assertEquals(0, $c);

		$c = Image::hex2percentage('19');
		$this->assertEquals(10, $c);

		$c = Image::hex2percentage('72');
		$this->assertEquals(45, $c);

		$c = Image::hex2percentage('7F');
		$this->assertEquals(50, $c);

		$c = Image::hex2percentage('D8');
		$this->assertEquals(85, $c);

		$c = Image::hex2percentage('FF');
		$this->assertEquals(100, $c);
	}

	public function testPercentage2hex()
	{
		$p = Image::percentage2hex(100);
		$this->assertEquals('ff', $p);

		$p = Image::percentage2hex(50);
		$this->assertEquals('7f', $p);

		$p = Image::percentage2hex(0.5);
		$this->assertEquals('7f', $p);

		$p = Image::percentage2hex(0);
		$this->assertEquals('00', $p);
	}

	public function testHex2rgba()
	{
		// string "#RRGGBB"
		$color = Image::hex2rgba('#0000ff');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1.0], $color);

		// string "RRGGBB"
		$color = Image::hex2rgba('0000ff');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1.0], $color);

		// string "#RGB"
		$color = Image::hex2rgba('#00f');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1.0], $color);

		// string "RGB"
		$color = Image::hex2rgba('00f');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1.0], $color);

		// string "#RRGGBBAA"
		$color = Image::hex2rgba('#0000FFCC');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 0.8], $color);

		// string "RRGGBBAA"
		$color = Image::hex2rgba('0000FF00');
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 0.0], $color);

		// array [0, 0, 255]
		$color = Image::hex2rgba([0, 0, 255]);
		$this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 0], $color);
	}

	public function testRgba2hex()
	{
		$color = Image::rgba2hex([0, 0, 0]);
		$this->assertEquals('#000000', $color);

		$color = Image::rgba2hex([0, 255, 0]);
		$this->assertEquals('#00ff00', $color);

		$color = Image::rgba2hex([0, 127, 255]);
		$this->assertEquals('#007fff', $color);

		$color = Image::rgba2hex([0, 127, 255, 0.0]);
		$this->assertEquals('#007fff00', $color);

		$color = Image::rgba2hex([0, 127, 255, 0.5]);
		$this->assertEquals('#007fff7f', $color);

		$color = Image::rgba2hex([0, 127, 255, 100]);
		$this->assertEquals('#007fffff', $color);
	}

	public function testReadExifData()
	{

		$image = Image::load(static::$imageExif, static::$lib);

		// extended info: false
		$info = $image->getInfo();
		$this->assertArrayNotHasKey('exif', $info);

		// extended info: true
		$info = $image->getInfo(true);
		$image->destroy();
		$this->assertArrayHasKey('exif', $info);

		// get exif without loading an image
		$image = new Image(null, static::$lib);
		$exif = $image->readExifData(static::$imageExif);

		$this->assertIsArray($exif);
		$this->assertArrayHasKey('FileName', $exif);
	}

	public function testGetExifProperty()
	{
		/*
		Image Exif:
		[ExposureTime] => 1/345
		[FNumber] => 80/10
		[ExposureProgram] => 2
		[ISOSpeedRatings] => 125
		 */

		// image without exif data
		$image = Image::load(static::$imageNoMetadata, static::$lib);
		$value = $image->getExifData('ExposureTime');
		$image->destroy();
		$this->assertNull($value);

		// image with exif data
		$image = Image::load(static::$imageExif, static::$lib);

		// direct property
		$value = $image->getExifData('ExposureTime');
		$this->assertEquals($value, '1/345');
		
		// multiple properties (callback is disabled in current version)
		$value = $image->getExifData(['DateTimeOriginal', 'ExposureTime']);
		$this->assertEquals(['2000:11:07 10:41:43', '1/345'], $value);

		// using named properties
		$dateCrated = $image->getExifData('date_created');
		$iso = $image->getExifData('iso');
		$exposure = $image->getExifData('exposure');

		$this->assertEquals('2000:11:07 10:41:43', $dateCrated);
		$this->assertEquals(125, $iso);
		$this->assertEquals('1/345', $exposure);

		// with custom callback
		$value = $image->getExifData('date_created', null, function($val, $exif) {
			$dt = \DateTime::createFromFormat('Y:m:d H:i:s', $val, new \DateTimeZone('UTC'));
			return $dt->format(\DateTime::ISO8601);
		});

		$this->assertEquals('2000-11-07T10:41:43+0000', $value);
	}
	
	public function testGetDateCreated()
	{
		$image = Image::load(static::$imageExif, static::$lib);
		
		// return object
		$dateCreated = $image->getDateCreated();		
		$this->assertIsObject($dateCreated);
		$this->assertInstanceOf('\DateTime', $dateCreated);
		
		// return string
		$dateCreated = $image->getDateCreated(\DateTime::ISO8601);		
		$this->assertIsString($dateCreated);
		$this->assertEquals('2000-11-07T10:41:43+0000', $dateCreated);
	}
	
	public function testGetGps()
	{
		// gps available
		$image = Image::load(static::$imageExifGps, static::$lib);
		
		// decimal
		$gps = $image->getGps();
		$this->assertIsArray($gps);
		$this->assertEquals(['lat' => 43.468365, 'lng' => 11.881634999972], $gps);
		
		// degrees minutes seconds (DMS)
		$gps = $image->getGps(true);
		$this->assertIsArray($gps);
		$this->assertEquals([
			'lat' => ['degrees' => 43, 'minutes' => 28, 'seconds' => 6.114], 
			'lng' => ['degrees' => 11, 'minutes' => 52, 'seconds' => 53.8859999]
		], $gps);
		$image->destroy();
		
		// gps not available
		$image = Image::load(static::$imageLandscape, static::$lib);
		$gps = $image->getGps();		
		$this->assertNull($gps);
	}











	

	public function testFetExtensionFromPath()
	{
		$im = Image::getInstance(static::$lib);

		$this->assertEquals('png', $im->getExtensionFromPath('C:\\test\\sample_image.png'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('C:\\test\\sample_image.new.jpg'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('sample_image.jpg'));
		$this->assertEquals('jpg', $im->getExtensionFromPath('/images/sample_image.jpg'));
	}

	public function testUpscaleCheck()
	{
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
	}


	
}