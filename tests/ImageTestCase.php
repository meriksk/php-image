<?php

class ImageTestCase extends PHPUnit_Framework_TestCase
{
	
	/**
	 * Image (Landscape orientation - 1920x1280px)
	 * @var string
	 */
	protected static $testImageLandscape;
	
	/**
	 * Image with EXIF data (Landscape orientation - 1920x1280px)
	 * @var string
	 */
	protected static $testImageLandscapeExif;

	/**
	 * Image (Portrait orientation - 1280x1920px)
	 * @var string
	 */
	protected static $testImagePortrait;
	
	/**
	 * Image (Landscape orientation - 500x333px)
	 * @var string
	 */
	protected static $testImageSmall;

	

    /**
     * This method is called before the first test of this test class is run.
     */
	public static function setUpBeforeClass(): void
	{
		self::$testImageLandscape = DIR_TEST_ASSETS . DS . 'img_1920x1280.jpg';
		self::$testImageLandscapeExif = DIR_TEST_ASSETS . DS . 'img_1920x1280_exif.jpg';
		self::$testImagePortrait = DIR_TEST_ASSETS . DS . 'img_1280x1920.jpg';
		self::$testImageSmall = DIR_TEST_ASSETS . DS . 'img_500x333.jpg';
	} 

    /**
     * This method is called after the last test of this test class is run.
     */
	public static function tearDownAfterClass(): void
    {
		//cleanDirectory(DIR_TEST_TMP);
	}
	
	/**
	 * Get temporary image path
	 * @param string $filename
	 * @return string
	 */
	public function getTmpPath($filename): string
	{
		return DIR_TEST_TMP . DS . static::$lib . '_' . $filename;
	}

	public function assertImage($image): void
	{
		$this->assertInstanceOf('meriksk\Image\Image', $image);
	}

	/**
	 * Asserts that a string is a valid JSON string.
	 * @param mixed $resource
	 * @param string $message
	 */
	public function assertResource($resource, $message = ''): void
	{
		if ($resource && ((is_resource($resource) && 'gd'===get_resource_type($resource)) || (is_object($resource) && ($resource instanceof Imagick)))) {
			$this->assertTrue(true);
		} else {
			$msg = !empty($message) ? $message : 'Invalid resource.';
			$this->fail($msg);
		}
	}
	
}
