<?php
declare(strict_types=1);

namespace meriksk\PhpImage\Tests;

use PHPUnit\Framework\TestCase;
use meriksk\PhpImage\Image;


abstract class BaseTestCase extends TestCase
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


    public static function setUpBeforeClass():void
    {

		// php settings
		ini_set('memory_limit', -1);
		ini_set('display_errors', E_ALL);
		ini_set('log_errors_max_len', 0);
		ini_set('zend.assertions', 1);
		ini_set('assert.exception', 1);
		ini_set('xdebug.show_exception_trace', 1);		

        //if (!extension_loaded(static::$lib)) {
        //    $I->markTestIncomplete('The "'. static::$lib.'" extension is not available.');
        //}

		Image::$debug = defined('DEBUG') && DEBUG===true;

		self::$imageLandscape = fixPath(DIR_TEST_ASSETS . DS . 'image_landscape.jpg');
		self::$imagePortrait = fixPath(DIR_TEST_ASSETS . DS . 'image_portrait.jpg');
		self::$imageTransparentPng = fixPath(DIR_TEST_ASSETS . DS . 'image_transparent.png');
		self::$imageTransparentGif = fixPath(DIR_TEST_ASSETS . DS . 'image_transparent.gif');
		self::$imageNoMetadata = fixPath(DIR_TEST_ASSETS . DS . 'image_no_metadata.jpg');
		self::$imageExif = fixPath(DIR_TEST_ASSETS . DS . 'image_exif.jpg');
		self::$imageExifGps = fixPath(DIR_TEST_ASSETS . DS . 'image_exif_gps.jpg');
		self::$imageRemoteExif = 'https://raw.githubusercontent.com/ianare/exif-samples/master/jpg/exif-org/canon-ixus.jpg';
		
		if (Image::$debug===true) {
			echo "\n";
		}
    }
	
	public static function tearDownAfterClass():void
	{
	}

    protected function setUp(): void
	{
		if (DEBUG) {
			echo "\n\n----------------------------------------------------------";
			echo "\n" . get_called_class() . ': ' . $this->getName();
			echo "\n----------------------------------------------------------\n";
		}		
	}

	/*
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
		if ($resource) {
			if (is_object($resource) && 'Imagick'===get_class($resource)) {
				$this->assertTrue(true);
				return;
			} else {
				if (\PHP_VERSION_ID >= 80000) {
					if ($resource instanceof \GdImage) {
						$this->assertTrue(true);
						return;
					}
				} else {
					if (is_resource($resource) && 'gd'===get_resource_type($resource)) {
						$this->assertTrue(true);
						return;
					}
				}
			}
		}
		
		// default
		$msg = !empty($message) ? $message : 'Invalid resource.';
		$this->fail($msg);
	}

}