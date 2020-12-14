<?php

namespace tests\unit;

use meriksk\PhpImage\Image;
use tests\unit\ImageTest;

class ImagickTest extends ImageTest
{

	protected static $lib = Image::DRIVER_IMAGICK;

}
