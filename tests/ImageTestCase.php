<?php

namespace tests;

class ImageTestCase extends \Codeception\Test\Unit
{

	protected static $lib;

	protected static $imageLandscape;			// local, landscape, 800x533
	protected static $imagePortrait;			// local, portrait, 533x800
	protected static $imageTransparentGif;		// local, portrait, 610x621
	protected static $imageTransparentPng;		// local, portrait, 610x621
	protected static $imageNoMetadata;			// local, portrait, 640x480
	protected static $imageExif;				// local, portrait, 640x480
	protected static $imageExifGps;				// local, landscape, 640x480
	protected static $imageRemoteExif;			// remote, portrat, 533x800 


    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Method is executed before each test
	 */
    public function _before()
    {
        //if (!extension_loaded(static::$lib)) {
        //    $I->markTestIncomplete('The "'. static::$lib.'" extension is not available.');
        //}

		self::$imageLandscape = DIR_TEST_ASSETS . DS . 'image_landscape.jpg';
		self::$imagePortrait = DIR_TEST_ASSETS . DS . 'image_portrait.jpg';
		self::$imageTransparentPng = DIR_TEST_ASSETS . DS . 'image_transparent.png';
		self::$imageTransparentGif = DIR_TEST_ASSETS . DS . 'image_transparent.gif';
		self::$imageNoMetadata = DIR_TEST_ASSETS . DS . 'image_no_metadata.jpg';
		self::$imageExif = DIR_TEST_ASSETS . DS . 'image_exif.jpg';
		self::$imageExifGps = DIR_TEST_ASSETS . DS . 'image_exif_gps.jpg';
		self::$imageRemoteExif = 'https://raw.githubusercontent.com/ianare/exif-samples/master/jpg/exif-org/canon-ixus.jpg';
	} 

	/**
	 * Method is executed after each test
	 */
	public function _after()
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
		$this->assertInstanceOf('meriksk\PhpImage\Image', $image);
	}

	/**
	 * Asserts that a string is a valid JSON string.
	 * @param mixed $resource
	 * @param string $message
	 */
	public function assertResource($resource, $message = '')
	{
		if ($resource && ((is_resource($resource) && 'gd'===get_resource_type($resource)) || (is_object($resource) && ($resource instanceof Imagick)))) {
			$this->assertTrue(true);
		} else {
			$msg = !empty($message) ? $message : 'Invalid resource.';
			$this->fail($msg);
		}
	}
	
}
