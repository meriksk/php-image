<?php
error_reporting(E_ALL);

// Directory separator
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// "tests" base directory
define('DIR_ROOT', dirname(__DIR__));

// "tests" base directory
define('DIR_TEST', __DIR__);

// "assets" directory
define('DIR_TEST_ASSETS', DIR_TEST . DS . 'assets');

// holds test generated images
define('DIR_TEST_TMP', DIR_TEST . DS . 'tmp'); 


include_once DIR_ROOT . '/vendor/autoload.php';
//include_once dirname(__DIR__).'/src/meriksk/Image/autoloader.php';

// -----------------------------------------------------------------------------
// FUNCTIONS
// -----------------------------------------------------------------------------

function cleanDirectory($directory) {
	$pattern = rtrim($directory, DS) . DS . '*.{jpg,png,gif}';
    $files = glob($pattern, GLOB_NOSORT|GLOB_BRACE);
	if ($files) {
		foreach ($files as $file) {
			if (is_writable($file)) {
				unlink($file);
			}
		}
	}
}
