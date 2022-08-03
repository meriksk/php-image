<?php

namespace meriksk\PhpImage\Tests\Image;

use meriksk\PhpImage\Tests\BaseTestCase;
use meriksk\PhpImage\Image;


class ImageTestCase extends BaseTestCase
{

	public function testConstruct()
	{
		// path
		$image = new Image(self::$imageLandscape, static::$lib);
		$this->assertImage($image);

		// data url
		$image = new Image('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gIcSUNDX1BST0ZJTEUAAQEAAAIMbGNtcwIQAABtbnRyUkdCIFhZWiAH3AABABkAAwApADlhY3NwQVBQTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA9tYAAQAAAADTLWxjbXMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAApkZXNjAAAA/AAAAF5jcHJ0AAABXAAAAAt3dHB0AAABaAAAABRia3B0AAABfAAAABRyWFlaAAABkAAAABRnWFlaAAABpAAAABRiWFlaAAABuAAAABRyVFJDAAABzAAAAEBnVFJDAAABzAAAAEBiVFJDAAABzAAAAEBkZXNjAAAAAAAAAANjMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB0ZXh0AAAAAEZCAABYWVogAAAAAAAA9tYAAQAAAADTLVhZWiAAAAAAAAADFgAAAzMAAAKkWFlaIAAAAAAAAG+iAAA49QAAA5BYWVogAAAAAAAAYpkAALeFAAAY2lhZWiAAAAAAAAAkoAAAD4QAALbPY3VydgAAAAAAAAAaAAAAywHJA2MFkghrC/YQPxVRGzQh8SmQMhg7kkYFUXdd7WtwegWJsZp8rGm/fdPD6TD////bAIQAAgICAgICAwMDAwQEAwQEBQUEBAUFCAYGBgYGCAwHCQcHCQcMCw0KCgoNCxMPDQ0PExYSERIWGhgYGiEgISwsOwECAgICAgIDAwMDBAQDBAQFBQQEBQUIBgYGBgYIDAcJBwcJBwwLDQoKCg0LEw8NDQ8TFhIREhYaGBgaISAhLCw7/8IAEQgAZABkAwEiAAIRAQMRAf/EADgAAQACAQUBAAAAAAAAAAAAAAAHCQgBAgMEBQYBAQABBQEBAQAAAAAAAAAAAAAIBAUGBwkBAgP/2gAMAwEAAhADEAAAAJgE6ON41NGzbZNgcrzPY+/x4heMFAAAB771ude0UQ77CWHVXfXSrkevoOEoeXYAADXQ9zK+Xk/KuE3XXCb4uw2snKNdQyJY8zgAAAMrM8K+rBYo9M9KgLXKjaSs4Xa6sseaIVFuAAAy8zgwZnqJ3Uea8C8vPkML2rWRv4eabXHwKuzAAASRvgeMNeS3sh8DAbsWbNZrdTt7cgsD4AAAAAAAA//EADcQAAICAQIEBAMFBgcAAAAAAAEDAgQFBhEABxITCBQhMCMxQRAVUXGRFjJAQoKSIkNik6Gxs//aAAgBAQABPwD+AG2/qdh9TwWU401dXnI35MrNnvNZqhNgAdgLEesMUGLmWmRBJlHYAcY50cy8oxkW5KwBIyTj1TusiI/OUo1hMxA/E8My1ArsRRZQyypTZdjuAMEobjacP3o7SG0gRuOLYoKcsU53JJjLyzpW5rnOb4Rke+vtRiILaVzHbluYGI9Tv7O0SCD+6QQfyPoeOXnLvOc0+Y+LqBVc4WjXU3U77BlBC0RmjZYiNu4bZrArG+0R3BP5DeliMRjWzZVp1q7JLWuc0pisla9+iBMQN4x3PSOOcXKvEa/0NksVTr46tlGvVbo2mrCwLy2RaDKa9p/F6eiZG5MSfQ8Y+jmcUieOyqCjK17rjk0k79mwuTfgmW20pGT5TJjvER6did/Z+fGm8Pj2+Hnytio16dU6kyJyVdMiGWaeMW2YrAj12bGoIf1HjkrrHI5nS2X1tQ5ivwmJGU+66GZv3GOx2TpARkmYTeZGdd6mTmqQJE5Ef4hxauvHO/N6HyWTzFrLUqMM7gnWbz2ztW66TdnmJLUe0iup4imCpAdRJ6ePERVqnmNXzFaAgrO4DH5CcQP82Jkky/Mw6B7IOxHHLHT2odU+HrCs08a37Q4PO5W1STaJiiwV2nKbWZKPrAOUwiM/XolsdjxqjPYTNJXS1HXzun2QKZPxj6qegtQ0PXMFsJwkYTG/UsmMhxj7GqdeUjg9MVMi7zcWKv6oyKQpNVDie5NW0QGsAJClQHSDtvsOPFEqtS5g4XHIHSqlplK4Q/CErEoxH6L9rwh5UP0hqHHyG06edZOA/wBFtC37/wB2/wBviHyC8lzoz/TLq8lVxlD8jBUrMv8A39rwjZFtfWWpqPV8OziqNkR/CddzFSP6MH2HjVGZOpNWagy/zjfzF96yPXdQaUqP+2uPteFASHNHIT/ljp5ol/XaXt/19nMG3nMfobUNvDUmXcqjF3GUKa5RjN1iKiVrgZkDeUuOT0+VdCePRrS9kLemriFVaGRsXJ0LNBwn2CLcKvQGTgz4bZk7qIB2MSSMhRhi8rk6ELE7C6d+1WVYnERm1S5/DnMD0EzAjq29N/Z8IWKLc1q7KSPotGNoLH4S+Jan/wATj9hG/HMLwZ5nPauzxwmoqNTRep7ofnsTbod9qu8BC15BnUO2bEI7EnfpJ4hXdUL0usMsOXatrdYaQZtmp81FktgBvLp9nwu58YzC8waqCv7wT5bKpg30jKHlux+gkn14xvPrAsrwlbx9pbukbhXQyH5xJMTxofXsdZtyE0UpIp1u1GLGTBnOctydxH0AA4hz60ne0vqLM1ounXwtF9qzPo+H85eXWJfzMeAJQiPoRxWjZhWULMhKz09T5D6tmeth/uJ9nk9qJmluaem7nzRatfdVyG24mjIbLAI+vS0QlxrbT2Y0HzwtaNoZi3WwLa8bOPriCXdiDYSYIQL4SIhEwkNt9o+gHEtQ5nQ3g8z2WGSsPymZsZNFS8Yri1a7VmdVMwVxjEFSRuPTjmLhK/LnkVy80fShGEMiyOQyc9zMunUTFoiyZJM5FkoEk7k9Ptat0/l9RY7y9HOW8S6Lq70XKR7dhLUMDYThMeo9Rxk+SlrUGPSnOaozmXvLfJ33pdylhlwmUTDo7kiSF7SIEB6cY7lTqbECnSpa21CrTdVldi9Ozyj3Y4Gvv0AKb1ARBO/SONMcnKeks0Mnj71qDIKaquhltz66IO6esJWwkQB6BxUVbVEh74tP0Ih0/wAF/8QAKhEAAgIBAwIFAwUAAAAAAAAAAQIDBBEFEiEABgcTIDFxEFFhFDBBQqH/2gAIAQIBAT8A+mndw6XqdaxPFKFhhmeJ3chVyuOQftz1b1zT6V6lUkc+ZcLiEjlcrzz8549XdEtCDVL8MZljq1bJjQbN67pXZR7HOWKnAAPHXhu1Kz3LRrymR0MYt1s4UZdNytwTwQD+cj1d7aNWm7nvmRSs0VuYpIp52s5YDn5yOvCHSK1XuuAQJgIk0sje5YldnPxu9XiTGkfed/b/AD5RPyY167L720DsrVg+orOZrq/p6nlplS+QSjMSArNxtH9uqlmK7VhsR58uVFdMjBwwyPT3zBcs926m5QkiVR+cbBj/ADqbTDaZYpayzGKaGRVZQ22VTlGH2OeqETQUa8TY3JEinHtwMcenX+z59V1x7kRjWN6oicNzmQE7X+R1pXh5bp3688ssbLHfaw4C43rgbB+Nh/b/AP/EADIRAAIBAwIFAAUNAAAAAAAAAAECAwQFEQYHABITICEIFCJRYRAwMTIzQUJScYORoaL/2gAIAQMBAT8A+TVe2GsdH3S3W2toneurqCCthp4FaV+nOCVUgD63jizbdaovunL/AHqmpx6pY1hauV8q4WViuVGPw489o+njSFXfNT6S0/WXSWFrk9qpUcqhTmWOMEZ+Khscb9XG9WDaXUKW94eWs6FNcGKZfoCQKUUn4v2jweNs9QO2grA6hZI5LbSM2fBD9JQ3HpKXx32tuSuFXrSU0Ma/urIf6Tu2Hmkn2nsDOSWCVC5PuWd1HG7u1uq90dM09LaZqaOnpaxZqzqs3P8AZuFKqitkDzzcXi11Vju1bbqoKKmkqJYJgpyOeNihwf1Hbs1BCu2GnhTYMfquTg59tnLP/rPFPX1NrWoZHREeIpPzqrIUyG9oOCPBAIP3EZ41FWpcr/cqtGZknq55QzeWIdy2T27N7u6b0ZomG33O6TJUJdDIkMULPy0zKpYMWwpBOeNcb8aMvNjvlHQXKoDV9sWOnHRI6cxMgdX93MrKMrnh1COQGDD8wzg/zj5r/9k=');
		$this->assertImage($image);

		// string (invalid filename - exception expected)
		$this->expectException(\InvalidArgumentException::class);
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

		// remote image
		// exif
		$image = new Image(self::$imageRemoteExif, static::$lib);
		$info = $image->ping();

		$this->assertEquals(self::$imageRemoteExif, $info['path']);
		$this->assertEquals(640, $info['width']);
		$this->assertEquals(480, $info['height']);
		$this->assertEquals('landscape', $info['orientation']);
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
		$this->assertNull($image->getMimeType());
		$this->assertNull($image->getWidth());
		$this->assertNull($image->getHeight());
		$this->assertNull($image->getExtension());
		$this->assertNull($image->getOrientation());
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
		$this->assertEquals([800, 533], $dimensions);

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

		$this->assertEquals(800, $w);

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

		$this->assertEquals(533, $h);
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
		$data = $image->toString(100, 'jpg');

		$this->assertIsString($data);

		// RE-CREATE THE IMAGE
		$image = Image::fromString($data);
		$info = $image->getInfo();

		$this->assertImage($image);
		$this->assertEquals('image/jpeg', $info['mime_type']);
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

		$this->assertEquals([800, 533], $image->getDimensions());
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

		$this->assertEquals([800, 533], $image->getDimensions());
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
	}

	public function testResizeToShortSide()
	{
		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToShortSide(600, true);
		$path = $this->getTmpPath('resizeToShortSide_600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([901, 600], $image->getDimensions());
		$this->assertEquals(901, $info['width']);
		$this->assertEquals(600, $info['height']);

		// test 2: landscape (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToShortSide(700, false);
		$path = $this->getTmpPath('resizeToShortSide_700.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([800, 533], $image->getDimensions());
		$this->assertEquals(800, $info['width']);
		$this->assertEquals(533, $info['height']);
	}

	public function testResizeToLongSide()
	{

		// test 1 (allowEnlarge: true)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToLongSide(600, true);
		$path = $this->getTmpPath('resizeToLongSide_600.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([600, 400], $image->getDimensions());
		$this->assertEquals(600, $info['width']);
		$this->assertEquals(400, $info['height']);

		// test 2: landscape (allowEnlarge: false)
		$image = new Image(static::$imageLandscape, static::$lib);
		$image->resizeToLongSide(700, false);
		$path = $this->getTmpPath('resizeToLongSide_700.jpg');
		$image->save($path);
		$info = $image->getInfo();

		$this->assertEquals([700, 466], $image->getDimensions());
		$this->assertEquals(700, $info['width']);
		$this->assertEquals(466, $info['height']);
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
		
		$this->markTestSkipped();
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
		
		$this->markTestIncomplete();
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

		$c = Image::hex2percentage('1A');
		$this->assertEquals(10, $c);

		$c = Image::hex2percentage('73');
		$this->assertEquals(45, $c);

		$c = Image::hex2percentage('80');
		$this->assertEquals(50, $c);

		$c = Image::hex2percentage('D9');
		$this->assertEquals(85, $c);

		$c = Image::hex2percentage('FF');
		$this->assertEquals(100, $c);
	}

	public function testPercentage2hex()
	{
		$p = Image::percentage2hex(0);
		$this->assertEquals('00', $p);

		$p = Image::percentage2hex(10);
		$this->assertEquals('1a', $p);

		$p = Image::percentage2hex(45);
		$this->assertEquals('73', $p);

		$p = Image::percentage2hex(50);
		$this->assertEquals('80', $p);

		$p = Image::percentage2hex(85);
		$this->assertEquals('d9', $p);

		$p = Image::percentage2hex(100);
		$this->assertEquals('ff', $p);
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
		$this->assertEquals('#007fff80', $color);

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
		$this->assertEquals(array(
			'DateTimeOriginal' => '2000:11:07 10:41:43',
			'ExposureTime' => '1/345',
		), $value);

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



}