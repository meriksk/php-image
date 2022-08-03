<?php

namespace meriksk\PhpImage\Tests\Image;

use meriksk\PhpImage\Image;
use meriksk\PhpImage\Tests\Image\BaseImageTest;


class ImagickTest extends BaseImageTest
{

	protected static $lib = Image::DRIVER_IMAGICK;

}
