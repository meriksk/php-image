<?php

namespace PHPImage;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use PHPImage\ImageFactory;

/**
 * Image class file
 */
class Image
{

    /**
     * Constant for the (deprecated) transparent color
     */
    const COLOR_TRANSPARENT = -1;

	/**
	 * @var int
	 */
	const FLIP_HORIZONTAL = 1;
	const FLIP_VERTICAL = 2;
	const FLIP_BOTH = 3;



	/**
	 * Allow up-scaling
	 */
	public static $allowUpscale = false;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $pathOriginal;

	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * @var Image Original image instance
	 */
	protected $imageOriginal;

	/**
	 * @var string
	 */
	protected $imageString;

	/**
	 * @var int
	 */
	protected $width;

	/**
	 * @var int
	 */
	protected $height;

	/**
	 * @var string Image orientation
	 */
	protected $orientation;

	/**
	 * @var string File MIME type
	 */
	protected $mimeType;

	/**
	 * @var string File extension
	 */
	protected $extension;

	/**
	 * @var array
	 */
	protected $info = [];

	/**
	 * @var int
	 */
	protected $quality = 82;

	/**
	 * Image EXIF data
	 * @var array
	 */
	protected $exifData;

	/**
	 * The date and time when the original image data was generated.
	 * @var int
	 */
	protected $dateCreated;

	/**
	 * @var array Watermark configuration
	 */
	protected static $watermark;

	/**
	 * @var bool
	 */
	protected static $debug = false;


	/**
	 * Load an image
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function getInstance($lib = NULL)
	{
		return ImageFactory::get($lib);
	}

	/**
	 * Destroy image resource
	 */
	public function __destruct()
	{
		$this->debug("__destruct()\t\tcalling -> destroy()");

		// working image
		$this->destroy();
	}

	/**
	 * Returns library used by script
	 * @return string
	 */
	public function getLib()
	{
		if (strpos(get_class($this), 'ImageGd')!==false) {
			return ImageFactory::LIB_GD;
		} else {
			return ImageFactory::LIB_IMAGICK;
		}
	}

	/**
	 * Load an image
	 * @param string $path Path to image file
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function load($path, $lib = NULL)
	{
		if (!file_exists($path)) {
			throw new Exception('Image is not readable or does not exist.');
		}

		$image = ImageFactory::get($lib);
		$image->loadImage($path);

		return $image;
	}

	/**
	 * Load a string as image
	 * @param string $string base64 string
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function loadString($string, $lib = NULL)
	{
		if (!is_string($string)) {
			throw new InvalidArgumentException('Invalid image data.');
		}

		if (!function_exists('getimagesizefromstring')) {
			throw new Exception('Required function "getimagesizefromstring()" does not exists.');
		}

		$image = ImageFactory::get($lib);
		$image->loadImage($string, true);

		return $image;
	}

	/**
	 * Load a base64 string as image
	 * @param string $base64string base64 string
	 * @param string $lib
	 * @return Image instance
	 * @throws Exception
	 */
	public static function loadBase64($base64string, $lib = NULL)
	{
		if (!is_string($string)) {

		}

		$string = base64_decode(str_replace(' ', '+',preg_replace('#^data:image/[^;]+;base64,#', '', $base64string)));

		if (!function_exists('getimagesizefromstring')) {
			throw new Exception('Required function "getimagesizefromstring()" does not exists.');
		}

		$image = ImageFactory::get($lib);
		$image->loadImage($string, true);

		return $image;
	}

	/**
	 * Create an image from scratch
	 * @param int $width  Image width
	 * @param int|null $height If omitted - assumed equal to $width
	 * @param null|string $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @param string $lib
	 * @return Image instance
	 */
	public static function create($width, $height = NULL, $color = NULL, $lib = NULL)
	{
		return ImageFactory::get($lib)->createImage($width, $height, $color);
	}

	/**
	 * Revert an image
	 * @return Image instance
	 */
	public function revert()
	{
		if (!empty($this->imageOriginal->resource) && $this->imageOriginal->isResource()) {

			$this->debug("revert()\t\tcalling -> revertImage()");
			$this->revertImage();
			$this->debug("revertImage()\t\tcalling -> readMetaData()");
			$this->readMetaData();
		}

		return $this;
	}

	/**
	 * Destroy image resources
	 * @param resource $image
	 * @return Image instance
	 */
	public function destroy()
	{
		$this->debug("destroy()\t\tcalling -> destroyResource()");

		$this->width = 0;
		$this->height = 0;
		$this->path = NULL;
		$this->extension = NULL;
		$this->orientation = NULL;
		$this->exifData = NULL;
		$this->mimeType = NULL;
		$this->path = NULL;
		$this->dateCreated = NULL;

		// resources
		if ($this->isResource()) {
			$this->destroyResource($this->resource);
		}
		if ($this->imageOriginal && $this->imageOriginal->isResource()) {
			$this->imageOriginal->destroyResource();
		}

		// references
		$this->imageOriginal = NULL;

		return $this;
	}

	/**
	 * Returns image resource
	 * @return resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * Returns instance of original image
	 * @return Image
	 */
	public function getImageOriginal()
	{
		return $this->imageOriginal;
	}

	/**
	 * Returns original image resource
	 * @return resource
	 */
	public function getImageOriginalResource()
	{
		return !empty($this->imageOriginal) ? $this->imageOriginal->getResource() : NULL;
	}

	/**
	 * Get image info
	 * @return array
	 */
	public function getInfo()
	{
		return [
			'path' => $this->path,
			'filename' => !empty($this->path) ? basename($this->path) : NULL,
			'width' => $this->width,
			'height' => $this->height,
			'orientation' => $this->orientation,
			'extension' => $this->extension,
			'mimeType' => $this->mimeType,
			'dateCreated' => $this->dateCreated,
		];
	}

	/**
	 * Returns image path
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns image dimensions
	 * @return array
	 */
	public function getDimensions()
	{
		return [$this->width, $this->height];
	}

	/**
	 * Returns image width
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * Returns image height
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Returns image extension
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * Returns image date of creation
	 * @param string $format
	 * @return string
	 */
	public function getDateCreated($format = NULL)
	{
		return $this->dateCreated;
	}

	/**
	 * Refresh image
	 * @return Image instance
	 */
	public function refresh()
	{
		$this->readMetaData();
		return $this;
	}

	/**
	 * Outputs a message to the console.
	 * @param string $msg Message
	 * @param bool $newLine If true, new line character will sent to the output
	 * @param bool $inline
	 */
	protected function debug($msg, $newLine = TRUE, $inline = FALSE)
	{
		if (self::$debug === true) {
			consoleLog($msg, $newLine, $inline);
		}
	}

	/**
	 * Fetch basic attributes about the image.
	 * @param string $path The filename to read the information from.
	 * @return $array
	 * @throws Exception
	 */
	public function ping($path)
	{
		$this->debug("ping()\t\t\tcalling -> pingImage(..". basename($path) . ")");
		return $this->pingImage($path);
	}

	/**
	 * Updates meta data of image
	 * @return array
	 */
	public function readMetaData()
	{
		$info = NULL;

		if ($this->isResource()) {

			$this->debug("readMetaData()\t\tcalling -> readImageMetaData()");
			$this->readImageMetaData();
			$info = $this->getInfo();
			$this->debug("readImageMetaData()\timage size: {$this->width} x {$this->height}, orientation: {$info['orientation']}");
		}

		return $info;
	}

	/**
	 * Is an image a valid image resource?
	 * @param resource $image
	 * @return bool
	 */
	public function isResource($image = NULL)
	{
		if ($image === NULL) {
			$image = $this->resource;
		}

		if ($this->getLib() === ImageFactory::LIB_GD) {
			return (is_resource($image) && 'gd'===get_resource_type($image));
		} else {
			return !empty($image) && 'Imagick'===get_class($image);
		}
	}

	/**
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param null|string $path If omitted - original file will be overwritten
	 * @param null|int $quality	Output image quality in percents 0-100
	 * @param null|string $format The format to use; determined by file extension if null
	 * @return Image instance
	 * @throws Exception
	 */
	public function save($path = NULL, $quality = NULL, $format = NULL)
	{
		// Determine quality, path, and format
		$quality = ($quality > 0 && $quality <= 100) ? (int)$quality : $this->quality;
		$path = !empty($path) ? $path : $this->path;

		if (!$format) {
			$ext = $this->getExtensionFromPath($path);
			$format = $ext ? $ext : $this->extension;
		}

		$this->debug("save()\t\t\tquality: $quality, format: $format, path: .." . basename($path));
		$this->debug("save()\t\t\tcalling -> saveImage()");

		return $this->saveImage($path, $quality, $format);
	}

	/**
	 * Get image orientation
	 * @param NULL|int $width
	 * @param NULL|int $height
	 * @return int|string
	 */
	public function getOrientation($width = NULL, $height = NULL)
	{
		$w = $width>0 ? (int)$width : $this->width;
		$h = $height>0 ? (int)$height : $this->height;

		if ($w > $h) {
			return 'landscape';
		} elseif ($w < $h) {
			return 'portrait';
		} else {
			return 'square';
		}
	}

	/**
	 * Auto-adjust photo orientation
	 */
	protected function autoRotate()
	{

		$this->debug("autoRotate()\t\tcalling -> getOrientation(true)");

		// adjust orientation if EXIF lib is available
		$this->debug("getOrientation()\tcalling -> getExifProperty('orientation')");
		$orientation = $this->getExifProperty('orientation');

		if (empty($orientation)) {
			return $this;
		}

		$usingImagick = get_class($this)==='CImageImagick';

		// correct EXIF rotation information
		if ($usingImagick) {
			$this->debug("autoRotate()\t\treseting orientation: " . Imagick::ORIENTATION_TOPLEFT);
			$this->resource->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
		}

		switch ($orientation) {
			case 2:
				$this->flip();
				break;
			case 3:
				$this->rotate(180);
				break;
			case 4:
				$this->rotate(180)->flip();
				break;
			case 5:
				$this->rotate(90)->flip();
				break;
			case 6:
				$this->rotate(90);
				break;
			case 7:
				$this->rotate(-90)->flip();
				break;
			case 8:
				$this->rotate(-90);
				break;
		}

		if ($usingImagick) {
			$this->resource->setImageProperty('Exif:Orientation', Imagick::ORIENTATION_TOPLEFT);
		}

		return $this;
	}

	/**
	 * Returns the file extension of the specified file
	 * @param string $path
	 * @return string
	 */
	function getExtensionFromPath($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Returns the image EXIF data.
	 * @param string $property
	 * @param string $path
	 * @return mixed
	 */
	public function getExifData($property = NULL, $path = NULL)
	{
		$data = NULL;

		// path set
		if ($path !== NULL) {
			$this->debug("getExifData(\"$property\")\t\tcalling -> readImageExifData(". ($path  ? '../'.basename($path).')' : ''). ")");
			$this->exifData = $this->readImageExifData($path);
		// current image path
		} elseif ($this->exifData === NULL) {
			$this->debug("getExifData(\"$property\")\t\tcalling -> readImageExifData(". ($this->path  ? '../'.basename($this->path).')' : ''). ")");
			$this->exifData = $this->readImageExifData($this->path);
		}

		if ($property !== NULL) {
			$this->debug("getExifData(\"$property\")\t\tcalling -> getExifProperty(\"$property\"");
			$data = $this->getExifProperty($property);
		} else {
			$data = $this->exifData;
		}

		return $data;
	}

	/**
	 * Get image property info
	 * @param string $property
	 * @param mixed $default
	 * @param Closure $callback
	 * @return mixed
	 */
	public function getExifProperty($property, $default = NULL, $callback = NULL)
	{

		$value = $default;

		// supperted properties
		// [0] -> named property
		// [1] -> GD, imagick property
		// [2] -> callback
		$callbacks = [
			'dateCreated' => ['DateTimeOriginal', function($val) {
				$dt = DateTime::createFromFormat('Y:m:d H:i:s', $val, new DateTimeZone('UTC'));
				return $dt->getTimestamp();
			}],
			'orientation' => ['Orientation', 'intval'],
		];

		$propertyName = NULL;
		$propertyCallback = NULL;

		if (isset($callbacks[$property])) {
			if (is_array($callbacks[$property])) {

				$item = $callbacks[$property];
				$propertyName = !empty($item[0]) ? $item[0] : NULL;

				if ($callback && is_callable($callback)) {
					$propertyCallback = $callback;
				} else {
					$propertyCallback = isset($item[1]) && is_callable($item[1]) ? $item[1] : NULL;
				}
			} elseif (is_string($callbacks[$property])) {
				$propertyName = $callbacks[$property];
			}
		} else {
			$propertyName = str_replace('exif:', '', $property);
		}

		// read EXIF data
		$this->debug("getExifProperty()\tproperty: $propertyName, calling -> getExifData()");
		$exif = $this->getExifData();

		if ($exif) {
			$found = false;
			if (isset($exif[$propertyName])) {
				$found = true;
			} elseif (isset($exif['exif:' . $propertyName])) {
				$propertyName = 'exif:' . $propertyName;
				$found = true;
			}


			// value found
			if ($found) {
				if ($propertyCallback !== NULL) {
					$value = call_user_func($propertyCallback, $exif[$propertyName]);
				} else {
					$value = $exif[$propertyName];
				}
			}
		}


		return $value;
	}

	/**
	 * Upscale check
	 * @param int $width
	 * @param int $height
	 * @return array
	 */
	public function upscaleCheck($width, $height)
	{
		// if upscale allowed or if it already fits, there's nothing to do
		if (self::$allowUpscale === true || ($width <= $this->width && $height <= $this->height)) {
			return [$width, $height];
		}

		// check width
		if ($width > $this->width) {
			$width = $this->width;
		}

		// check height
		if ($height > $this->height) {
			$height = $this->height;
		}

		return [$width, $height];
	}

	/**
	 * Fit to width (proportionally resize to specified width)
	 * @param int $width
	 * @return Image instance
	 */
	public function fitToWidth($width)
	{
		// calculate aspect ratio
		$aspectRatio = $this->height / $this->width;

		$height = round($width * $aspectRatio);
		$this->debug("fitToWidth($width)\t\tcalling -> resize($width, $height)");
		$this->resize($width, $height);

		return $this;
	}

	/**
	 * Fit to height (proportionally resize to specified height)
	 * @param int $height
	 * @return Image instance
	 */
	public function fitToHeight($height)
	{
		// calculate aspect ratio
		$aspectRatio = $this->height / $this->width;

		$width = round($height / $aspectRatio);
		$this->debug("fitToHeight($height)\tcalling -> resize($width, $height)");
		$this->resize($width, $height);

		return $this;
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int|array $width Desired width - number or array [w, h]
	 * @param int|null $height Desired height, if omitted - assumed equal to $width
	 * @return Image instance
	 */
	public function resize($width, $height = NULL)
	{
		// support array definition
		if (is_array($width) && isset($width[0]) && isset($width[1])) {
			$height = $width[1];
			$width = $width[0];
		}

		$w = $width > 0 ? (int)$width : 300;
		$h = $height > 0 ? (int)$height : $w;

		// upscale check
		list ($w, $h) = $this->upscaleCheck($w, $h);

		$this->debug("resize($width, $height)\tcalling -> resizeImage($w, $h)");
		$this->resizeImage($w, $h);
		$this->debug("resizeImage($w, $h)\tcalling -> readMetaData()");
		$this->readMetaData();

		return $this;
	}

	/**
	 * Best fit (proportionally resize to fit in specified width/height)
	 * Shrink the image proportionally to fit inside a $width x $height box
	 * @param int $width
	 * @param int|null $height
	 * @return Image
	 */
	public function bestFit($width, $height = NULL)
	{
		// max width & height
		$w = $width > 0 ? (int)$width : 300;
		$h = $height > 0 ? (int)$height : $w;

		// if it already fits, there's nothing to do
		if ($this->width <= $w && $this->height <= $h) {
			return $this;
		}

		// determine aspect ratio of the current image
		$aspectRatio = $this->height / $this->width;

		// Make width fit into new dimensions
		if ($this->width > $w) {
			$newWidth = $w;
			$newHeight = round($w * $aspectRatio);
		} else {
			$newWidth = $this->width;
			$newHeight = $this->height;
		}

		// Make height fit into new dimensions
		if ($newHeight > $h) {
			$newHeight = $h;
			$newWidth = round($newHeight / $aspectRatio);
		}

		$this->debug("bestFit($width, $height)\tcalling -> resize($newWidth, $newHeight)");
		$this->resize($newWidth, $newHeight);

		return $this;
	}

	/**
	 * Thumbnail
	 * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
	 * remaining overflow (from the center) to get the image to be the size specified.
	 *
	 * @param int|array $width Desired width - number or array [w, h]
	 * @param int|null $height Desired height, if omitted - assumed equal to $width
	 * @param bool $shrink
	 * @return Image instance
	 * @throws Exception
	 */
	public function thumbnail($width, $height = NULL, $shrink = FALSE)
	{
		// support array definition
		if (is_array($width) && isset($width[0]) && isset($width[1])) {
			$shrink = ($height===true);
			$height = $width[1];
			$width = $width[0];
		}

		$this->debug("thumbnail($width, $height, ". ($shrink===true?'true': 'false') . ")");
		$desiredWidth = $width > 0 ? (int)$width : 300;
		$desiredHeight = $height > 0 ? (int)$height : $desiredWidth;

		// determine aspect ratios
		$aspectRatioBefore = $this->width / $this->height;
		$aspectRatioAfter = $desiredWidth / $desiredHeight;

		$cropWidth = 0;
		$cropHeight = 0;

		if ($shrink === true) {

			list ($w, $h) = $this->upscaleCheck($desiredWidth, $desiredHeight);

			//var_dump($this->getDimensions());
			//var_dump([$w, $h]);

			// landscape
			if ($aspectRatioBefore > $aspectRatioAfter) {
				$this->fitToWidth($w);
			// portrait
			} else {
				$this->fitToHeight($h);
			}

			$cropWidth = $desiredWidth;
			$cropHeight = $desiredHeight;

		} else {

			// desired thumb dimensions are larger than current image dimmensions
			if ($desiredWidth > $this->width || $desiredHeight > $this->height) {

				// both dimensions are larger
				if ($desiredWidth > $this->width && $desiredHeight > $this->height) {

					// there's nothing to do

				// desired width is larger
				} elseif ($desiredWidth > $this->width) {

					if ($aspectRatioBefore > $aspectRatioAfter) {
						$this->fitToHeight($desiredHeight);
						$cropWidth = $this->width;
						$cropHeight = $desiredHeight;
					} else {
						$this->fitToWidth($this->width);
						$cropWidth = $this->width;
						$cropHeight = $desiredHeight;
					}

				} elseif ($desiredHeight > $this->height) {

					if ($desiredWidth > $this->width) {
						$cropWidth = $this->width;
						$cropHeight = $this->height;
					} else {
						$cropWidth = $desiredWidth;
						$cropHeight = $this->height;
					}

				}

			// desired thumb dimensions fits current image dimmensions
			} else {

				if ($aspectRatioBefore > $aspectRatioAfter) {
					$this->fitToHeight($desiredHeight);
				} else {
					$this->fitToWidth($desiredWidth);
				}

				$cropWidth = $desiredWidth;
				$cropHeight = $desiredHeight;
			}

		}


		if ($cropWidth > 0 && $cropHeight > 0) {
			$this->debug("thumbnail($width, $height, ". ($shrink?'true': 'false') . ")\tcalling -> cropImage($cropWidth, $cropHeight, ". ($shrink?'true': 'false') . ")");

			// return trimmed image
			$this->cropImage($cropWidth, $cropHeight, $shrink);
			$this->debug("cropImage($cropWidth, $cropHeight, ". ($shrink?'true': 'false') . ")\tcalling -> readMetaData()");
			$this->readMetaData();
		}

		return $this;
	}

	/**
	 * Rotates an image.
	 * @param int $angle Rotation angle in degrees. Supports negative values.
	 * @param mixed $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Transparent by default.
	 * @return \CImage
	 */
	public function rotate($angle, $bgColor = self::COLOR_TRANSPARENT)
	{
		$angle = (int)$angle;
		if (!is_numeric($angle) || $angle===0 || $angle<-359 || $angle > 359) {
			return $this;
		}

		$this->debug("rotate($angle)\t\tcalling -> rotateImage($angle)");
		$this->rotateImage($angle, $bgColor);
		$this->debug("rotateImage($angle)\tcalling -> readMetaData()");
		$this->readMetaData();

		return $this;
	}

	/**
	 * Flips an image using a given mode
	 * @param int $mode
	 * @return $this
	 */
	public function flip($mode = self::FLIP_VERTICAL)
	{
		if ($this->resource) {

			$allowedModes = [
				'horizontal' => self::FLIP_HORIZONTAL,
				self::FLIP_HORIZONTAL => self::FLIP_HORIZONTAL,
				'vertical' => self::FLIP_VERTICAL,
				self::FLIP_VERTICAL => self::FLIP_VERTICAL,
				'both' => self::FLIP_BOTH,
				self::FLIP_BOTH => self::FLIP_BOTH,
			];

			if (isset($allowedModes[$mode])) {
				$this->debug("flip($mode) calling -> flipImage($mode)");
				$this->flipImage($mode);
				$this->debug("flip($mode) calling -> readMetaData()");
				$this->readMetaData();
			}
		}

		return $this;
	}

	/**
	 * Opposite color
	 * @param string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @param bool $inverse
	 * @return array
	 */
	public function oppositeColor($color, $inverse = false)
	{
		// sharp
		$sharp = (is_string($color) && strpos($color, '#')===0);

		// normalize color
		$color = $this->normalizeColor($color);

		// inversed color
		if ($inverse) {
			$r = (strlen($r=dechex(255 - $color['r']))<2) ? '0'.$r : $r;
			$g = (strlen($g=dechex(255 - $color['g']))<2) ? '0'.$g : $g;
			$b = (strlen($b=dechex(255 - $color['b']))<2) ? '0'.$b : $b;
			return ($sharp ? '#' : '') . $r.$g.$b;
		// monotone based on darkness of original
		} else {
			return ($sharp ? '#' : '') . (array_sum($color) > (255*1.5)) ? '000000' : 'FFFFFF';
		}
	}

	/**
	 * Converts a hex color value to its RGB equivalent
	 * @param string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @return array|bool
	 */
	protected function normalizeColor($color)
	{
		if (is_string($color)) {
			$color = trim($color, '#');
			if (strlen($color) === 6) {
				list($r, $g, $b) = [
					$color[0].$color[1],
					$color[2].$color[3],
					$color[4].$color[5]
				];
			} elseif (strlen($color) === 3) {
				list($r, $g, $b) = [
					$color[0].$color[0],
					$color[1].$color[1],
					$color[2].$color[2]
				];
			} else {
				$r = '00';
				$g = '00';
				$b = '00';
			}

			return [
				'r' => hexdec($r),
				'g' => hexdec($g),
				'b' => hexdec($b),
				'a' => 0
			];
		}

		return false;
	}

	/**
	 * Ensures $value is always within $min and $max range.
	 * If lower, $min is returned. If higher, $max is returned.
	 *
	 * @param int|float $value
	 * @param int|float $min
	 * @param int|float $max
	 * @return int|float
	 */
	protected function keepWithin($value, $min, $max)
	{
		if ($value < $min) {
			return $min;
		} elseif ($value > $max) {
			return $max;
		} else {
			return $value;
		}
	}

	/**
	 * Replace accented characters with non accented
	 * @param string $string
	 * @return string
	 */
	public function removeAccents($string)
	{
		$arr = [
			'before' => ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή'],
			'after' => ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η']
		];

		return str_replace($arr['before'], $arr['after'], $string);
	}

	/**
	 * Set watermark configuration
	 * @param string $path
	 * @param array $config
	 * @return bool
	 */
	public function setWatermark($path, $config = [])
	{
		// already loaded
		if (self::$watermark !== NULL) {
			return self::$watermark!==false;
		}

		if ($path && !file_exists($path)) {
			self::$watermark = false;
			return false;
		}

		// load
		$cfg = is_array($config) ? $config : [];

		self::$watermark = [
			'width' => 0,
			'height' => 0,
			'position' => (!empty($cfg['position']) ? $cfg['position'] : 'middle-bottom'),
			'offsetX' => (!empty($cfg['offsetX']) ? $cfg['offsetX'] : 0),
			'offsetY' => (!empty($cfg['offsetY']) ? $cfg['offsetY'] : -15),
			'image' => NULL,
		];

		return $this->loadImageWatermark($path);
	}

	/**
	 * Add watermark to image resource
	 */
	public function addWatermark()
	{
		if (self::$watermark && $this->resource) {
			$this->debug("addWatermark()\t\tposition: ". self::$watermark['position'] . ", offsetX: ". self::$watermark['offsetX'] . ", offsetY: ". self::$watermark['offsetY']);

			$origin = $this->alignWatermark(
				self::$watermark['position'],
				self::$watermark['width'],
				self::$watermark['height'],
				$this->width,
				$this->height,
				self::$watermark['offsetX'],
				self::$watermark['offsetY']
			);

			$this->debug("addWatermark()\t\tcalling -> addImageWatermark(". implode(", ", $origin) . ")");
			$result = $this->addImageWatermark($origin);

			if (!$result) {
				$this->debug("addWatermark()\t\t!!! FAILED");
			}
		}
	}

	/**
	 * Align watermark stamp
	 *
	 * @param string $position
	 * @param int $stampWidth
	 * @param int $stamHeight
	 * @param int $sourceWidth
	 * @param int $sourceHeight
	 * @param int $offsetX Watermark X offset
	 * @param int $offsetY Watermark Y offset
	 * @return bool
	 */
	protected function alignWatermark($position, $stampWidth, $stamHeight, $sourceWidth, $sourceHeight, $offsetX = 0, $offsetY = 0)
	{
		if (!empty($position) && ($stampWidth>0) && ($stamHeight > 0) && ($sourceWidth > 0) && ($sourceHeight > 0)) {

			if (($stampWidth > $sourceWidth) or ($stamHeight > $sourceHeight)) {
				return false;
			}

			$x = $y = 0;

			switch ($position) {

				case 'left-top':
					$x = $y = 0;
					break;
				case 'left-middle':
					$x = 0;
					$y = ($sourceHeight / 2) - ($stamHeight / 2);
					break;
				case 'left-bottom':
					$x = 0;
					$y = $sourceHeight - $stamHeight;
					break;
				case 'middle-top':
					$x = ($sourceWidth / 2) - ($stampWidth / 2);
					$y = 0;
					break;
				case 'center':
					$x = ($sourceWidth / 2) - ($stampWidth / 2);
					$y = ($sourceHeight / 2) - ($stamHeight / 2);
					break;
				case 'middle-bottom':
					$x = ($sourceWidth / 2) - ($stampWidth / 2);
					$y = $sourceHeight - $stamHeight;
					break;
				case 'right-top':
					$x = $sourceWidth - $stampWidth;
					$y = 0;
					break;
				case 'right-middle':
					$x = $sourceWidth - $stampWidth;
					$y = ($sourceHeight / 2) - ($stamHeight / 2);
					break;
				case 'right-bottom':
					$x = $sourceWidth - $stampWidth;
					$y = $sourceHeight - $stamHeight;
					break;
				default:
					return false;
			}

			// offset
			$x = $x + $offsetX; $y = $y + $offsetY;

			return array($x, $y);
		}
	}

}
