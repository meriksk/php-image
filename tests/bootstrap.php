<?php

error_reporting(E_ALL);

// Directory separator
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// "tests" base directory
define('DIR_ROOT', dirname(__DIR__));

// "tests" base directory
define('DIR_TEST', __DIR__);

// "assets" directory
define('DIR_TEST_ASSETS', DIR_TEST . '/assets');

// holds test generated images
define('DIR_TEST_TMP', DIR_TEST . '/tmp');

include_once DIR_ROOT . '/vendor/autoload.php';
include_once DIR_TEST . '/functions.php';
//include_once dirname(__DIR__).'/src/meriksk/Image/autoloader.php';

// debug mode
define('DEBUG', false);

// clean tmp directory once before the test suite runs
cleanDirectory(DIR_TEST_TMP);
