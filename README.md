PHP-Image Library
======================

[![Latest Stable Version](http://img.shields.io/github/release/meriksk/php-image.svg)](https://packagist.org/packages/meriksk/php-image)
[![License](https://poser.pugx.org/meriksk/php-image/license)](https://packagist.org/packages/meriksk/php-image)

> The Image class offers a bunch of image processing features using GD or IMagick.

## Requirements

- PHP >=7.0

## Supported Image Libraries

- GD Library (>=2.0)
- Imagick PHP extension (>=6.5.7)

------------------

## Setup

The preferred way to install this extension is through [Composer](https://getcomposer.org/).
If you do not have [Composer](https://getcomposer.org/), you may install it by following the instructions at [getcomposer.org](https://getcomposer.org/).

Either run

```
composer require meriksk/php-image
```

or add 

```
"meriksk/php-image": "~1.0" 
```

to your composer.json and run composer update

## Usage

* [Open image](#open-image)
* [Resize image](#resize-image)

Open image
----------

Because this class uses namespacing, when instantiating the object, you need to either use the fully qualified namespace:

```php
$image = new \meriksk\Image\Image($filename);
```

or alias it:

```php 
use meriksk\Image\Image;

$image = new Image($filename);
```

This class can use Imagick or GD extension - whichever is available.
Imagick extension is preferred if is available. You can force the extension for manipulating images as follows:

```php
$image = new Image($filename, Image::DRIVER_GD);
```

Save image
----------

Library supports three formats of image: 'jpeg', 'png' and 'gif'. By default they quality is set to 75. When saving to disk or outputting into the browser, the script assumes the same output type and quality as input.

```php
$image->save($filename);
```

Save in a different type to the source:

```php
$image->save($filename, 60, 'png');
```

Output image
----------

To render the image directly into the browser, you can call:

```php
$image->output(60, 'png');
```

Resize
------------

**resize**

```php
$image = $image->resize($width, $height, $allow_enlarge);
```

**resize to width**

```php
$image = $image->resizeToWidth($width, $allow_enlarge);
```

**resize to height**

```php
$image = $image->resizeToHeight($height, $allow_enlarge);
```

**resize to best fit**

```php
$image = $image->resizeToBestFit($max_width, $max_height, $allow_enlarge);
```

**resize to long side**

```php
$image = $image->resizeToLongSide($max_width, $max_height, $allow_enlarge);
```

**resize to short side**

```php
$image = $image->resizeToShortSide($max_width, $max_height, $allow_enlarge);
```


Crop
----------

```php
$image->crop($width, $height, $width, Image::::CROP_CENTER, $allow_enlarge);
```

Thumbnail
----------

```php
$image->thumbnail($width, $height, $width, $allow_enlarge);
```

Rotate image
------------

Rotating is counter clockwise;

Rotate on 90 degrees:

```php
$image->rotate(90);
```

Rotate on 45 degrees, and fill empty field with white color:

```php
$image->rotate(45, '#FFFFFF');
```

Flip image
----------

Flip in vertical direction:

```php
$image->flip();
```

Flip in horisontal direction:

```php
$image->flip(Image::FLIP_HORIZONTAL);
```

Flip in both directions:

```php
$image->flip(Image::FLIP_BOTH);
```

Filters
-------

todo
