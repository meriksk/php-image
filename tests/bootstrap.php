<?php

error_reporting(E_ALL);

include_once dirname(__DIR__).'/vendor/autoload.php';

// Directory separator
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// "tests" base directory
define('DIR_TEST', __DIR__);

// "assets" directory
define('DIR_TEST_ASSETS', DIR_TEST . '/assets');

// holds test generated images
define('DIR_TEST_TMP', DIR_TEST . '/tmp'); 


// backward compatibility with PHP 5.5, PHPUnit 4.8 and PHPUnit 6.2
if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}
if (!class_exists('PHPUnit_Framework_Error_Notice') && class_exists('\PHPUnit\Framework\Error\Notice')) {
    class_alias('\PHPUnit\Framework\Error\Notice', 'PHPUnit_Framework_Error_Notice');
}

include_once __DIR__.'/ImageTestCase.php';
include_once __DIR__.'/ImageTest.php';


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

function testIsResource($resource) {
	return $resource && (
		(is_resource($resource) && 'gd'===get_resource_type($resource))
		||
		(is_object($resource) && 'Imagick'===get_class($resource))
	);
}