PHP-Image Library
======================

[![Latest Stable Version](http://img.shields.io/github/release/meriksk/php-image.svg)](https://packagist.org/packages/meriksk/php-image)
[![License](https://poser.pugx.org/meriksk/php-image/license)](https://packagist.org/packages/meriksk/php-image)

> A perceptual hash is a fingerprint of a multimedia file derived from various features from its content. Unlike cryptographic hash functions which rely on the avalanche effect of small changes in input leading to drastic changes in the output, perceptual hashes are "close" to one another if the features are similar.

PHP-Image Library

PHP image handling and manipulation library with support for both GD and Imagick extensions.

It can send HTTP requests to a Bitcoin peer server to perform several operations.

Currently it can get transaction details, get peer information, get mining information, get network hashes, get block chains, etc..

## Installation

You may install this library through Composer. You can also view more info about this on [Packagist].

## Requirements

- PHP >=7.0

## Supported Image Libraries

- GD Library (>=2.0)
- Imagick PHP extension (>=6.5.7)

## Getting started

```
composer require meriksk/php-image
```

or add "meriksk/php-image":"~1.0" to your composer.json and run composer update

```json
{
    "require": {
        "meriksk/php-image": "1.*"
    }
}
```

## Usage

* [Open image](#open-image)
* [Resize image](#resize-image)

Open image
----------

Create instance of image:

```php
$image = \merik\Image\Image::load($pathToImage);
```

or add to top of your file:

```php 
use merik\Image\Image;
```

and then load image:

```php
$image = Image::load($pathToImage);
```

Factory incapsulates instantiating of all image objects and allow to confirure created images:

```php
$factory = new \merik\Image\ImageFactory;
```

Opening from filename:

```php
$factory->openImage('/path/to/image.jpeg');
```

Opening from GD resource:

```php
$factory->openImage($imageResource);
```

Creating new image:
```
$image = $factory->createImage(300, 200);
```

Resize image
------------

There is four resize modes: 'scale', 'fit', 'crop' and 'cache'.

```php
$newImage = $factory->resizeImage($image, $mode, $width, $height);
```

If you want to register own resize strategy, extend class from \Sokil\Image\AbstractResizeStrategy and add namespase:
```php
// through factory constructor
$factory = new \Sokil\Image\Factory([
    'namespace' => [
        'resize' => '\Vendor\ResizeStrategy',
    ],
]);
// through factory method
$factory->addResizeStrategyNamespace('\Vendor\ResizeStrategy');
// directly to image
$image->addResizeStrategyNamespace('\Vendor\ResizeStrategy');
```
Classes searches in priority of adding.

Crop image
----------

To get part of image by specified wifth and height and in defined coordinates use:
```php
$x = 10;
$y = 10;
$width = 20;
$height = 20;

$image->crop($x, $y, $width, $height);
```

Rotate image
------------

Rotating is counter clockwise;

Rotate on 90 degrees:
```php
$image->rotate(90);
```

Rotate on 45 degrees, and fill empty field with black color:
```php
$image->rotate(45, '#000000');
```

Rotate on 45 degrees, and fill empty field with transparent green color:
```php
$image->rotate(45, '#8000FF00');
```

Flip image
----------

Flip in vertical direction:
```php
$image->flipVertical();
```

Flip in horisontal direction
```php
$image->flipHorisontal();
```

Flip in both directions
```php
$image->flipBoth();
```

Filters
-------

Greyscale image:
```php
$factory->filterImage($image, 'greyscale');
```

If you want to register own filter strategy to support new filters, extend class from \Sokil\Image\AbstractFilterStrategy and add namespase:
```php
// through factory constructor
$factory = new \Sokil\Image\Factory([
    'namespace' => [
        'filter' => '\Vendor\FilterStrategy',
    ],
]);
// through factory method
$factory->addFilterStrategyNamespace('\Vendor\FilterStrategy');
// or directly to image
$image->addFilterStrategyNamespace('\Vendor\FilterStrategy');
```
Classes searches in priority of adding.

Image elements
--------------

### Adding elements to image

Element is everything that can me append to image: text, shape, other image. First we need to create element instabce and configure it:
```php
$someElement = $factory->createElement('someElement')->setParam1('someValue');
```

Than element placed to image to some coordinates:
```php
$image->appendElementAtPosition($someElement, 30, 30);
```

You can create your own elements that inherits \Sokil\Image\AbstractElement class, and register namespace:
```php
namespace Vendor\Elements;

class Circle extends \Sokil\Image\AbstractElement
{
    public function setRadius($r) { // code to set radius }
    
    public function draw($resource, $x, $y) 
    {
        // code to draw circle on image $resouce at coordinates ($x, $y)
    }
}

// through factory constructor
$factory = new \Sokil\Image\Factory([
    'namespace' => [
        'element' => '\Vendor\Element',
    ],
]);
// through factory method
$factory->addElementNamespace('\Vendor\Elements');
```

Now you can draw your own circles:
```php
$circle = $factory->createElement('circle')->setRadiud(100);
$image->appendElementAtPosition($circle, 100, 100);
```

### Writing text

To create text element you can use one of methods: 
```php
$textElement = $factory->createElement('text');
// or through helper 
$textElement = $factory->createTextElement();
```

First we need to configure text element:
```php
$factory = new \Sokil\Image\Factory();
        
// text element
$element = $factory
    ->createTextElement()
    ->setText('hello world')
    ->setAngle(20)
    ->setSize(40)
    ->setColor('#ababab')
    ->setFont(__DIR__ . '/FreeSerif.ttf');
```

Now we need to place element in image at some coordinates:
```php
$image->appendElementAtPosition($element, 50, 150);
```

Save image
----------

Library supports three formats of image: 'jpeg', 'png' and 'gif'. 

To write image to disk you must define format of image and configure write strategy:
```php
$factory->writeImage($image, 'jpeg', function(\Sokil\Image\WriteStrategy\JpegWriteStrategy $strategy) {
    $strategy->setQuality(98)->toFile('/path/to/file.jpg');
});
```

To send image to STDOUT you must define format of image and configure write strategy:
```php
$factory->writeImage($image, 'jpeg', function(\Sokil\Image\WriteStrategy\JpegWriteStrategy $strategy) {
    $strategy->setQuality(98)->toStdout();
});
```

If you want to register own write strategy to support new image format, extend class from \Sokil\Image\AbstractWriteStrategy and add namespase:
```php
// through factory constructor
$factory = new \Sokil\Image\Factory([
    'namespace' => [
        'write' => '\Vendor\WriteStrategy',
    ],
]);
// through factory method
$factory->addWriteStrategyNamespace('\Vendor\WriteStrategy');
// or directly to image
$image->addWriteStrategyNamespace('\Vendor\WriteStrategy');
```
Classes searches in priority of adding.