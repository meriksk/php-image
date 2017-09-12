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
     * This method is called before the first test of this test class is run.
     */
	public static function setUpBeforeClass() 
	{
		self::$testImageLandscape = DIR_TEST_ASSETS . DS . 'img_1920x1280.jpg';
		self::$testImageLandscapeExif = DIR_TEST_ASSETS . DS . 'img_1920x1280_exif.jpg';
		self::$testImagePortrait = DIR_TEST_ASSETS . DS . 'img_1280x1920.jpg';
	} 

    /**
     * This method is called after the last test of this test class is run.
     */
	public static function tearDownAfterClass()
    {
		cleanDirectory(DIR_TEST_TMP);
	}
	
	/**
	 * Get temporary image path
	 * @param string $testName
	 * @return string
	 */
	public function getTmpPath($testName)
	{
		return DIR_TEST_TMP . DS . $testName .'_'. static::$lib .'.jpg';
	}

	/**
	 * Asserts that a string is a valid JSON string.
	 * @param mixed $value
	 * @param string $message
	 */
	public function assertResource($value, $message = '')
	{
		if ($value && (is_resource($value) || (is_object($value) && ($value instanceof Imagick)))) {
			// ok
		} else {
			$msg = !empty($message) ? $message : 'Invalid resource.';
			$this->fail($msg);
		}
	}
	
}
