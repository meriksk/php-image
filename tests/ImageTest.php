<?php

use meriksk\Image\ImageFactory;
use meriksk\Image\Image;


class ImageTest extends ImageTestCase
{

	/**
	 * @var string
	 */
	protected static $lib;


    public static function setUpBeforeClass(): void
    {
		parent::setUpBeforeClass();

        if (!extension_loaded(static::$lib)) {
            static::markTestSkipped('The "'. static::$lib.'" extension is not available.');
        }
    }

	public function testConstruct(): void
	{
		// path
		$image = new Image(self::$testImageSmall, static::$lib);
		$this->assertImage($image);

		// data url
		$image = new Image('data:image/gif;base64,R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
		$this->assertImage($image);

		// string (invalid filename - throws exception)
		$this->expectException(\Exception::class);
		$image = new Image('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
		$this->assertImage($image);
	}

	public function testGetInstance(): void
    {
		$image = Image::getInstance(static::$lib);
		$this->assertImage($image);
	}

	public function testLoad(): void
	{
		$image = new Image(null, static::$lib);
		$image->load(self::$testImageSmall);

		$this->assertImage($image);
		$this->assertNotNull($image->driver);
		$this->assertInstanceOf('meriksk\Image\driver\Image'. static::$lib, $image->driver);
	}

	public function testLoadFromString(): void
	{
		$image = Image::loadFromString(base64_decode('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw=='));
		$this->assertImage($image);
	}

	public function testLoadFromBase64String(): void
	{
		$image = Image::loadFromBase64String('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
		$this->assertImage($image);
	}

	public function testPing(): void
	{
		$image = new Image(null, static::$lib);
		$info = $image->ping(self::$testImageSmall);

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(self::$testImageSmall, $info['path']);
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
		//$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);
	}

	public function testGetInfo(): void
	{
		$image = new Image(self::$testImageSmall, static::$lib);
		$info = $image->getInfo();

		$this->assertIsArray($info);
		$this->assertArrayHasKey('path', $info);
		$this->assertEquals(self::$testImageSmall, $info['path']);
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);
		//$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mime_type']);
	}

	public function testDestroy(): void
	{
		$image = new Image(self::$testImageSmall, static::$lib);
		$image->destroy();

		// image
		$this->assertNull($image->getPath());
		$this->assertNull($image->getResource());
		$this->assertNull($image->getWidth());
		$this->assertNull($image->getHeight());
		$this->assertNull($image->getExtension());
		//$this->assertNull($image->getDateCreated());
	}

	public function testRevert(): void
	{
		$image = new Image(self::$testImageSmall, static::$lib);
		$image->destroy();
		$info = $image->getInfo();

		$this->assertNull($info['path']);
		$this->assertNull($info['width']);
		$this->assertNull($info['height']);

		$image->revert();
		$info = $image->getInfo();

		$this->assertEquals(self::$testImageSmall, $info['path']);
		$this->assertEquals(500, $info['width']);
		$this->assertEquals(333, $info['height']);

		/*
		// resize
		$im->thumbnail(300, 100);
		//$this->assertEquals([300, 100], $im->getDimensions());
		var_dump($im->getInfo());
		var_dump($im->getDimensions());
		return;
		$tmpPath = $this->getTmpPath('testThumbnail_' . microtime(true) . '.jpg');
		$im->save($tmpPath, 100);

		// revert
		$im->revert();
		var_dump($im->getInfo());
		$this->assertEquals($info, $im->getInfo());
		*/
	}

	public function testGetResource(): void
	{
		$image = new Image(self::$testImageSmall, static::$lib);
		$resource = $image->getResource();

		$this->assertResource($resource);
	}

	public function testGetDimensions(): void
	{
		$image = new Image(static::$testImageSmall, static::$lib);
		$dimensions = $image->getDimensions();

		$this->assertIsArray($dimensions);
		$this->assertEquals([500, 333], $dimensions);
	}

	public function testGetWidth(): void
	{
		$image = new Image(static::$testImageSmall, static::$lib);

		$this->assertEquals(500, $image->getWidth());
	}

	public function testGetHeight(): void
	{
		$image = new Image(static::$testImageSmall, static::$lib);

		$this->assertEquals(333, $image->getHeight());
	}

	public function testGetMimeType(): void
	{
		$image = new Image(static::$testImageSmall, static::$lib);

		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testGetExtension(): void
	{
		$image = new Image(static::$testImageSmall, static::$lib);

		$this->assertEquals('jpg', $image->getExtension());
		$this->assertEquals('.jpg', $image->getExtension(true));
	}

	public function testGetOrientation(): void
	{
		// test 1 (landscape)
		$image = new Image(static::$testImageSmall, static::$lib);
		$this->assertEquals('landscape', $image->getOrientation());
		
		// test 2 (portrait)
		$image = new Image(static::$testImagePortrait, static::$lib);
		$this->assertEquals('portrait', $image->getOrientation());
	}

	public function testSave(): void
	{

		// test 1
		$image = new Image(static::$testImageSmall, static::$lib);
		$path = $this->getTmpPath('test_save_1.jpg');
		$result = $image->save($path);

		$this->assertTrue($result);
		$this->assertFileExists($path);

		// test 2
		$image = new Image(static::$testImageSmall, static::$lib);
		$path = $this->getTmpPath('test_save_2.png'); $image->save($path, 80, 'png');
		$this->assertFileExists($path);
	}

	public function testResize(): void
	{
		// working image
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1
		$image->resize(200, 200);
		$this->assertEquals([200, 200], $image->getDimensions());

		// test 2 (allowEnlarge: false)
		$image->revert();
		$image->resize(600, 600);
		$this->assertEquals([500, 333], $image->getDimensions());

		// test 3 (allowEnlarge: enabled)
		$image->revert();
		$image->resize(600, 600, true);
		$this->assertEquals([600, 600], $image->getDimensions());
	}

	public function testResizeToWidth(): void
	{
		// working image
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->resizeToWidth(300);
		//$path = $this->getTmpPath('test_resize_to_width_1.jpg'); $image->save($path);
		$this->assertEquals([300, 200], $image->getDimensions());

		// test 2 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToWidth(600, true);
		$this->assertEquals([600, 400], $image->getDimensions());
	}

	public function testResizeToHeight(): void
	{
		// working image
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->resizeToHeight(300);
		//$path = $this->getTmpPath('test_resize_to_height_1.jpg'); $image->save($path);
		$this->assertEquals([450, 300], $image->getDimensions());

		// test 2 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToHeight(600, true);
		//$path = $this->getTmpPath('test_resize_to_height_2.jpg'); $image->save($path);
		$this->assertEquals([901, 600], $image->getDimensions());
	}

	public function testResizeToShortSide(): void
	{
		// working image
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->resizeToShortSide(200);
		//$path = $this->getTmpPath('test_resize_to_short_side_1.jpg'); $image->save($path);
		$this->assertEquals([300, 200], $image->getDimensions());

		// test 2 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToShortSide(500, true);
		//$path = $this->getTmpPath('test_resize_to_short_side_2.jpg'); $image->save($path);
		$this->assertEquals([750, 500], $image->getDimensions());
	}

	public function testResizeToLongSide(): void
	{
		// working image
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->resizeToLongSide(300);
		//$path = $this->getTmpPath('test_resize_to_long_side_1.jpg'); $image->save($path);
		$this->assertEquals([300, 199], $image->getDimensions());

		// test 2 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToLongSide(700, true);
		//$path = $this->getTmpPath('test_resize_to_long_side_2.jpg'); $image->save($path);
		$this->assertEquals([700, 466], $image->getDimensions());
	}

	public function testResizeToBestFit(): void
	{
		// working image (500x333)
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->resizeToBestFit(200, 200);
		//$path = $this->getTmpPath('test_resize_to_best_fit_1.jpg'); $image->save($path);
		$this->assertEquals([200, 133], $image->getDimensions());

		// test 2 (allowEnlarge: false)
		$image->revert();
		$image->resizeToBestFit(600, 600);
		//$path = $this->getTmpPath('test_resize_to_best_fit_2.jpg'); $image->save($path);
		$this->assertEquals([500, 333], $image->getDimensions());

		// test 3 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToBestFit(600, 350, true);
		//$path = $this->getTmpPath('test_resize_to_best_fit_3.jpg'); $image->save($path);
		$this->assertEquals([525, 350], $image->getDimensions());

		// test 4 (allowEnlarge: enabled)
		$image->revert();
		$image->resizeToBestFit(200, 200, true);
		//$path = $this->getTmpPath('test_resize_to_best_fit_4.jpg'); $image->save($path);
		$this->assertEquals([200, 133], $image->getDimensions());
	}

	public function testCrop(): void
	{
		// working image (500x333)
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->crop(100, 250, Image::CROP_LEFT);
		//$path = $this->getTmpPath('test_crop_1.jpg'); $image->save($path);
		$this->assertEquals([100, 250], $image->getDimensions());

		// test 2 (allowEnlarge: false)
		$image->revert();
		$image->crop(100, 250, Image::CROP_CENTER);
		//$path = $this->getTmpPath('test_crop_2.jpg'); $image->save($path);
		$this->assertEquals([100, 250], $image->getDimensions());

		// test 3 (allowEnlarge: false)
		$image->revert();
		$image->crop(100, 250, Image::CROP_RIGHT);
		//$path = $this->getTmpPath('test_crop_3.jpg'); $image->save($path);
		$this->assertEquals([100, 250], $image->getDimensions());

		// test 4 (allowEnlarge: false)
		$image->revert();
		$image->crop(250, 100, Image::CROP_TOP);
		//$path = $this->getTmpPath('test_crop_4.jpg'); $image->save($path);
		$this->assertEquals([250, 100], $image->getDimensions());

		// test 5 (allowEnlarge: false)
		$image->revert();
		$image->crop(250, 100, Image::CROP_CENTER);
		//$path = $this->getTmpPath('test_crop_5.jpg'); $image->save($path);
		$this->assertEquals([250, 100], $image->getDimensions());

		// test 6 (allowEnlarge: false)
		$image->revert();
		$image->crop(250, 100, Image::CROP_BOTTOM);
		//$path = $this->getTmpPath('test_crop_6.jpg'); $image->save($path);
		$this->assertEquals([250, 100], $image->getDimensions());
	}

	public function testThumbnail(): void
	{
		// working image (500x333)
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1 (allowEnlarge: false)
		$image->thumbnail(300, 150);
		//$path = $this->getTmpPath('test_thumbnail_1.jpg'); $image->save($path);
		$this->assertEquals([300, 150], $image->getDimensions());

		// test 2 (allowEnlarge: false)
		$image->revert();
		$image->thumbnail(500, 500);
		//$path = $this->getTmpPath('test_thumbnail_2.jpg'); $image->save($path);
		$this->assertEquals([500, 333], $image->getDimensions());

		// test 3 (allowEnlarge: true)
		$image->revert();
		$image->thumbnail(500, 500, true);
		//$path = $this->getTmpPath('test_thumbnail_3.jpg'); $image->save($path);
		$this->assertEquals([500, 500], $image->getDimensions());
	}

	public function testRotate(): void
	{
		// working image (500x333)
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1
		$image->rotate(90);
		//$path = $this->getTmpPath('test_rotate_1.jpg'); $image->save($path);
		$this->assertEquals([333, 500], $image->getDimensions());

		// test 2
		$image->revert();
		$image->rotate(-90);
		//$path = $this->getTmpPath('test_rotate_2.jpg'); $image->save($path);
		$this->assertEquals([333, 500], $image->getDimensions());
	}

	public function testFlip(): void
	{
		// working image (500x333)
		$image = new Image(static::$testImageSmall, static::$lib);

		// test 1
		$image->flip(Image::FLIP_VERTICAL);
		//$path = $this->getTmpPath('test_flip_vertical.jpg'); $image->save($path);
		$this->assertEquals([500, 333], $image->getDimensions());

		// test 2
		$image->revert();
		$image->flip(Image::FLIP_HORIZONTAL);
		//$path = $this->getTmpPath('test_flip_horizontal.jpg'); $image->save($path);
		$this->assertEquals([500, 333], $image->getDimensions());

		// test 3
		$image->revert();
		$image->flip(Image::FLIP_BOTH);
		//$path = $this->getTmpPath('test_flip_both.jpg'); $image->save($path);
		$this->assertEquals([500, 333], $image->getDimensions());
	}

























	public function testGetDateCreated(): void
	{
		$this->markTestIncomplete();
	}

	public function testRefresh(): void
	{
		$this->markTestIncomplete();
	}



	public function testReadMetaData()
	{
		$path = static::$testImageLandscapeExif;
		$im = Image::load($path, static::$lib);
		$info = $im->readMetaData();
		$im->destroy();

		$this->assertNotEmpty($info);
		$this->assertEquals($path, $info['path']);
		$this->assertEquals(1920, $info['width']);
		$this->assertEquals(1280, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
		$this->assertEquals('jpg', $info['extension']);
		$this->assertEquals('image/jpeg', $info['mimeType']);
		$this->assertEquals(1444401939, $info['dateCreated']);
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
		$exif = $im->getExifData(NULL, static::$testImageLandscapeExif);

		$this->assertNotEmpty($exif);
		$this->assertTrue(is_array($exif));
		$this->assertArrayHasKey($exifKey, $exif);
	}

	public function testGetExifProperty()
	{
		$im = Image::load(static::$testImageLandscapeExif, static::$lib);
		$time = $im->getExifProperty('ExposureTime', static::$testImageLandscapeExif);
		$this->assertEquals($time, '1/50');
	}

	public function testUpscaleCheck()
	{
		// working image
		$im = Image::load(static::$testImageLandscape, static::$lib);

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