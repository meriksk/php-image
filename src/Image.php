<?php

namespace meriksk\PhpImage;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use meriksk\PhpImage\DriverFactory;

/**
 * Image class file
 */
class Image
{

    const COLOR_TRANSPARENT = -1;
    const COLOR_WHITE = 'FFFFFF';
    const COLOR_BLACK = '000000';

	const DRIVER_GD = 'gd';
	const DRIVER_IMAGICK = 'imagick';
	
	const ORIENTATION_LANDSCAPE = 'landscape';
	const ORIENTATION_PORTRAIT = 'portrait';
	const ORIENTATION_SQUARE = 'square';
	
    const CROP_CENTER = 1;
    const CROP_LEFT = 2;
    const CROP_RIGHT = 3;
    const CROP_TOP = 4;
    const CROP_BOTTOM = 5;
    const CROP_TOP_LEFT = 6;
    const CROP_TOP_RIGHT = 7;
    const CROP_BOTTOM_LEFT = 8;
    const CROP_BOTTOM_RIGHT = 9;

	const FLIP_HORIZONTAL = 'horizontal';
	const FLIP_VERTICAL = 'vertical';
	const FLIP_BOTH = 'both';
	
	public static $allowUpscale = false;
	public static $debug = false;
	public $driver;


	/**
	 * Class constructor
	 * @param string $filename
	 * @param string $driver
	 * @throws Exception
	 */
	public function __construct($filename = null, $driver = null)
	{
		// init driver
		$this->driver = DriverFactory::get($driver);

		// load image
		if ($filename !== null) {
			$this->driver->loadFromFile($filename);
		}
	}

	/**
	 * Load an image
	 * @param string $driver
	 * @return static
	 * @throws Exception
	 */
	public static function getInstance($driver = null)
	{
		return new Image(null, $driver);
	}

	/**
	 * Destroy image resource
	 */
	public function __destruct()
	{
		$this->destroy();
	}
	
	public function __toString() 
	{
		$this->output(100);
	}

	/**
	 * Returns driver used by script
	 * @return BaseImage
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Returns driver name used by script
	 * @return string
	 */
	public function getDriverName()
	{
		if (strpos(get_class($this->driver), 'DriverGd')!==false) {
			return self::DRIVER_GD;
		} else {
			return self::DRIVER_IMAGICK;
		}
	}
	
	/**
	 * Loads an image from a file.
	 *
	 * @param string $file The image file to load.
	 * @param string $driver Image library driver
	 * @return \meriksk\PhpImage
	 * @throws \Exception Thrown if file or image data is invalid.
	 */
	public static function load($file, $driver = null)
	{
		$image = new Image(null, $driver);
		$image->driver->loadFromFile($file);
		return $image;
	}

	/**
	 * Loads an image from a file.
	 *
	 * @param string $file The image file to load.
	 * @return \meriksk\PhpImage
	 * @throws \Exception Thrown if file or image data is invalid.
	 */
	public function loadFromFile($file)
	{
		$this->driver->loadFromFile($file);
		return $this;
	}
	
	/**
	 * Loads an image from a file.
	 *
	 * @param string $file The image file to load.
	 * @param string $driver Image library driver
	 * @return \meriksk\PhpImage
	 * @throws \Exception Thrown if file or image data is invalid.
	 */
	public static function fromFile($file, $driver = null)
	{
		$image = new Image(null, $driver);
		$image->driver->loadFromFile($file);
		return $image;
	}

	/**
	 * Loads an image from a string.
	 *
	 * @param string $data The raw image data as a string.
	 * @return \meriksk\PhpImage
	 * @throws \Exception Thrown if file or image data is invalid.
	 */
	public function loadFromString($data)
	{
		$this->driver->loadFromString($data, true);
		return $this;
	}
	
	/**
	 * Creates a new image from a string.
	 * @param string $data The raw image data as a string.
	 * @param string $driver Image library driver
	 * @return \meriksk\PhpImage
	 */
	public static function fromString($data, $driver = null)
	{
		$image = new Image(null, $driver);
		$image->driver->loadFromString($data, true);
		return $image;
	}

	/**
	 * Creates a new image from a base64 encoded string.
	 * @param string $string The raw image data encoded as a base64 string.
	 * @return \meriksk\PhpImage
	 */
	public function loadFromBase64($data)
	{
		$this->driver->loadFromString($data, false);
		return $this;
	}

	/**
	 * Creates a new image from a base64 encoded string.
	 * @param string $data The raw image data encoded as a base64 string.
	 * @param string $driver Image library driver
	 * @return \meriksk\PhpImage
	 */
	public static function fromBase64($data, $driver = null)
	{
		$image = new Image(null, $driver);
		$image->driver->loadFromString($data, false);
		return $image;
	}

	/**
	 * Fetch basic attributes about the image.
	 * @param string $filename This parameter specifies the file you wish to 
	 * retrieve information about. It can reference a local file 
	 * or (configuration permitting) a remote file using one of the supported streams.
	 * @return array
	 * @throws Exception
	 */
	public function ping($filename = null)
	{
		return $this->driver->ping($filename);
	}
	
	/**
	 * Fetch basic attributes about the image.
	 * @param string $filename This parameter specifies the file you wish to 
	 * retrieve information about. It can reference a local file 
	 * or (configuration permitting) a remote file using one of the supported streams.
	 * @param string $driver Image library driver
	 * @return array
	 * @throws Exception
	 */
	public static function pingImage($filename, $driver = null)
	{
		$image = new Image(null, $driver);
		return $image->driver->ping($filename);
	}

	/**
	 * Get image info
	 * @param bool $extendedInfo Read extended information about the image (Exif, Gps, ...)
	 * @return array
	 */
	public function getInfo($extendedInfo = false)
	{
		return $this->driver->getInfo($extendedInfo);
	}

	/**
	 * Destroy image resources
	 * @return static
	 */
	public function destroy()
	{
		$this->driver->destroy();
		return $this;
	}

	/**
	 * Revert an image
	 * @return static
	 */
	public function revert()
	{
		$this->driver->revert();
		return $this;
	}
	
	/**
	 * Returns image resource
	 * @return resource
	 */
	public function getResource()
	{
		return $this->driver->resource;
	}

	/**
	 * Returns image path
	 * @return string
	 */
	public function getPath()
	{
		return $this->driver->path;
	}
	
	/**
	 * Returns the file extension of the specified file
	 * @param string $path
	 * @return string
	 */
	function getExtensionFromPath($path = null)
	{
		if ($path===null) { 
			$path = $this->driver->path;
		}

		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Returns image dimensions
	 * @return array Returns image dimensions or false on errors.
	 */
	public function getDimensions()
	{
		return $this->driver->getDimensions();
	}

	/**
	 * Returns image width
	 * @return int
	 */
	public function getWidth()
	{
		return $this->driver->w;
	}

	/**
	 * Returns image height
	 * @return int
	 */
	public function getHeight()
	{
		return $this->driver->h;
	}

	/**
	 * Returns the MIME content type
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->driver->mime_type;
	}

	/**
	 * Returns image extension
	 * @param bool $dot
	 * @return string
	 */
	public function getExtension($dot = false)
	{
		return $this->driver->extension ? ($dot===true ? '.' : '') . $this->driver->extension : null;
	}
	
	/**
	 * Get image orientation
	 * @param null|int $width
	 * @param null|int $height
	 * @return null|string
	 */
	public function getOrientation()
	{
		if ($this->driver->resource) {
			$w = $this->driver->w;
			$h = $this->driver->h;

			if ($w > $h) {
				return 'landscape';
			} elseif ($w < $h) {
				return 'portrait';
			} else {
				return 'square';
			}
		}
		
		return null;
	}
	
	/**
	 * Checks if an image is in required format.
	 * @return bool
	 */
	public function isImageType($imageType)
	{
		if (is_string($imageType)) {
			$imageType = strtolower(trim($imageType));
		}
		
		switch ($imageType) {
			case 'gif':
			case 'image/gif':
			case IMAGETYPE_GIF:
				return $this->driver->mime_type === 'image/gif';

			case 'png':
			case 'image/png':
			case IMAGETYPE_PNG:
				return $this->driver->mime_type === 'image/png';

			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
			case IMAGETYPE_JPEG:
				return $this->driver->mime_type === 'image/jpeg';
		}
		
		// default
		return false;
	}
	
	/**
	 * Checks if an image is in JPEG format.
	 * @return bool
	 */
	public function isJpeg()
	{
		return $this->isImageType('image/jpeg');
	}
	
	/**
	 * Checks if an image is in PNG format.
	 * @return bool
	 */
	public function isPng()
	{
		return $this->isImageType('image/png');
	}
	
	/**
	 * Checks if an image is in GIF format.
	 * @return bool
	 */
	public function isGif()
	{
		return $this->isImageType('image/gif');
	}

	/**
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $filename If omitted - original file will be overwritten
	 * @param int $quality	Output image quality in percents 0-100
	 * @param string $imageType The image type to use; determined by file extension if null
	 * @return bool
	 * @throws Exception
	 */
	public function save($filename = null, $quality = null, $imageType = null)
	{
		return $this->driver->save($filename, $quality, $imageType);
	}

	/**
	 * Generates an image string.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string 
	 */
	public function toString($quality = 100, $imageType = null) 
	{
		return $this->driver->toString($quality, $imageType);
	}

	/**
	 * Generates a base64 string.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string 
	 */
	public function toBase64($quality = 100, $imageType = null)
	{
		return $this->driver->toBase64($quality, $imageType);
	}
	
	/**
	 * Generates a data URI.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string Returns a string containing a data URI.
	 */
	public function toDataUri($quality = 100, $imageType = null)
	{
		return $this->driver->toDataUri($quality, $imageType);
	}
	
	/**
	 * Outputs the image to the screen. Must be called before any output is sent to the screen.
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType If omitted or null - image type of original file will be used (extension or mime-type)
	 * @return void
	 */
	public function toScreen($quality = null, $imageType = null)
	{
		$this->driver->toScreen($quality, $imageType);
	}
	
	/**
	 * Outputs the image to the browser as an attachment to be downloaded to local storage.
	 * @param string If omitted - original file will be used
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType If omitted or null - image type of original file will be used (extension or mime-type)
	 * @return void
	 */
	public function download($filename = null, $quality = null, $imageType = null)
	{
		$this->driver->download($filename, $quality, $imageType);
	}
	
	/**
	 * Resize an image to the specified dimensions
	 * @param int|array $width Desired width - number or array [w, h]
	 * @param int|null $height Desired height, if omitted - assumed equal to $width
	 * @param bool $allowEnlarge
	 * @param string|array $bgColor
	 * @return static
	 */
	public function resize($width, $height, $allowEnlarge = true, $bgColor = null)
	{
		$this->driver->resize($width, $height, $allowEnlarge, $bgColor);
		return $this;
	}

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToWidth($width, $allowEnlarge = true, $bgColor = null)
    {
		$this->driver->resizeToWidth($width, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given height (width proportional)
     * @param int $height
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToHeight($height, $allowEnlarge = true, $bgColor = null)
    {
		$this->driver->resizeToHeight($height, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given short side (long side proportional)
     * @param int $max
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToShortSide($max, $allowEnlarge = true, $bgColor = null)
    {
        $this->driver->resizeToShortSide($max, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given long side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToLongSide($max, $allowEnlarge = true, $bgColor = null)
    {
        $this->driver->resizeToLongSide($max, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image to best fit inside the given dimensions
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge = true, $bgColor = null)
    {
        $this->driver->resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge, $bgColor);
		return $this;
    }

    /**
     * Resizes image to worst fit inside the given dimensions
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
	 * @param string|array $bgColor
     * @return static
     */
    public function resizeToWorstFit($maxWidth, $maxHeight, $allowEnlarge = true, $bgColor = null)
    {
        $this->driver->resizeToWorstFit($maxWidth, $maxHeight, $allowEnlarge, $bgColor);
		return $this;
    }

	/**
	 * Crops image according to the given coordinates.
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @param string|array $bgColor
	 * @return static
	 */
	public function crop($x, $y, $width, $height, $allowEnlarge = false, $bgColor = null)
	{
		$this->driver->crop($x, $y, $width, $height, $allowEnlarge, $bgColor);
		return $this;
	}

	/**
	 * Crops image according to the given width, height and crop position.
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @param int $position
	 * @param string|array $bgColor
	 * @return static
	 */
	public function cropAuto($width, $height, $position = self::CROP_CENTER, $bgColor = null)
	{
		$this->driver->cropAuto($width, $height, $position, $bgColor);
		return $this;
	}

	/**
	 * Extracts a region of the image.
	 * @param int $width
	 * @param int $height
	 * @param bool $fill
	 * @param bool $enlarge
	 * @param string|array $bgColor
	 * @return static
	 * @throws Exception
	 */
	public function thumbnail($width, $height, $fill = false, $enlarge = true, $bgColor = null)
	{
		// check desired dimensions
		if (!is_numeric($width) || $width < 0) {
			throw new InvalidArgumentException("Width must be a valid int.");
		}
		if (!is_numeric($height) || $height < 0) {
			throw new InvalidArgumentException("Height must be a valid int.");
		}

		$this->driver->thumbnail($width, $height, $fill, $enlarge, $bgColor);
		return $this;
	}
	
	/**
	 * Flips an image using a given mode
	 * @param int|string $mode
	 * @return static
	 * @throws Exception
	 */
	public function flip($mode = self::FLIP_VERTICAL)
	{
		if (!in_array($mode, array(Image::FLIP_HORIZONTAL, Image::FLIP_VERTICAL, Image::FLIP_BOTH))) {
			throw new InvalidArgumentException('Invalid flip mode.');
		}

		$this->driver->flip($mode);
		return $this;
	}

	/**
	 * Rotates an image.
	 * @param int $angle Rotation angle in degrees. Supports negative values.
	 * @param string|array $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Transparent by default.
	 * @return static
	 */
	public function rotate($angle, $bgColor = null)
	{
		if (!is_numeric($angle) || $angle<-360 || $angle>360) {
			throw new InvalidArgumentException('Width must be a valid int.');
		}

		$this->driver->rotate($angle, $bgColor);
		return $this;
	}
	
	/**
	 * Auto-adjust photo orientation
	 */
	protected function autoRotate()
	{
		$this->debug("autoRotate()");

		// adjust orientation if EXIF lib is available
		$orientation = $this->getExifData('orientation');

		if (empty($orientation)) {
			return $this;
		}

		$driverImagick = get_class($this->driver)==='ImageImagick';

		// correct EXIF rotation information
		if ($driverImagick) {
			$this->debug("autoRotate()\t\treseting EXIF orientation: " . Imagick::ORIENTATION_TOPLEFT);
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

		if ($driverImagick) {
			$this->resource->setImageProperty('Exif:Orientation', Imagick::ORIENTATION_TOPLEFT);
		}

		return $this;
	}

	/**
	 * Set background color
	 * @param string|array $bgColor
	 * @return $this
	 */
	public function setBackgroundColor($color)
	{
		$this->driver->setBackgroundColor($color);
		return $this;
	}

	/**
	 * Normalize color
	 * @param string|array $color
	 * @param string|array $defaultColor
	 * @return array RGBa value
	 */
	public static function normalizeColor($color, $defaultColor = null)
	{
		if ($color===null) {
			return array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0.0);
		}
		
		if ($color) {
			if (is_array($color)) {
				$hex = self::rgba2hex($color);
				return self::hex2rgba($hex);
			} else {				
				return self::hex2rgba((string)$color);
			}
		}

		// default
		return $c;
	}
	
	/**
	 * Opposite color
	 * @param string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @param boolean $inverse
	 * @return array
	 */
	public static function oppositeColor($color, $inverse = false)
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
	 * Convert HEX value to it's color percentage representation (00 => 0%, 7F => 50%, FF => 100%)
	 * @param string $hex value (00-FF)
	 * @param bool $floating
	 * @return float
	 */
	public static function hex2percentage($hex, $floating = false)
	{
		if (is_string($hex) && strlen($hex)===2) {
			$val = hexdec($hex)/255;
			return $floating===true ? round($val, 2) : round($val*100);
		}
		
		return 100;
	}
	
	/**
	 * Convert percentage value to it's HEX color representation (0% => 00, 50% => 7F, 100% => FF)
	 * @param int $percentage value (0 - 100 or 0.0 - 1.0)
	 * @return float
	 */
	public static function percentage2hex($percentage)
	{	
		if (is_numeric($percentage)) {
			if ($percentage > 1) {
				$percentage = $percentage / 100; 
			}

			$val = ceil($percentage * 255);
			return str_pad(dechex($val), 2, 0, STR_PAD_LEFT);
		}
		
		return 'FF';
	}

	/**
	 * Converts a HEX color value to its RGB equivalent
	 * @param string|array $color
	 * Where red, green, blue - ints 0-255, alpha - int 0-255
	 * @return array|bool
	 */
	public static function hex2rgba($color)
	{
		// default color (transparent)
		$r = 255; $g = 255; $b = 255; $a = 1.0;

		if (is_string($color)) {
			$color = trim(strtolower($color), '#');

			switch ($color) {
				
				// black
				case 'black':
				case '000':
				case '000000':
				case '000000ff':
				case self::COLOR_BLACK:
					list ($r, $g, $b, $a) = array(0, 0, 0, 1.0);
					break;

				// white
				case 'white':
				case 'fff':
				case 'ffffff':
				case 'ffffffff':
				case self::COLOR_WHITE:
					list ($r, $g, $b, $a) = array(255, 255, 255, 1.0);
					break;
	
				// transparent
				case 'transparent':
				case '00000000':
				case 'ffffff00':
				case self::COLOR_TRANSPARENT:
					list ($r, $g, $b, $a) = array(255, 255, 255, 0.0);
					break;
				
				default:
					// 8-digit hexadecimal notation (with alpha)
					if (strlen($color) === 8) {
						list ($r, $g, $b, $a) = array(hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]), self::hex2percentage($color[6].$color[7], true));
					// 6-digit hexadecimal notation (no alpha)
					} elseif (strlen($color) === 6) {
						list ($r, $g, $b) = array(hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]));
					// 4-digit hexadecimal notation
					} elseif (strlen($color) === 4) {
						list ($r, $g, $b, $a) = array(hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]), self::hex2percentage($color[3].$color[3], true));
					// 3-digit hexadecimal notation (no alpha).
					} elseif (strlen($color) === 3) {
						list ($r, $g, $b) = array(hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]));
					}
				break;
			}

		} elseif (is_array($color)) {
			$r = isset($color[0]) && $color[0]>=0 && $color[0]<=255 ? $color[0] : 0;
			$g = isset($color[1]) && $color[1]>=0 && $color[1]<=255 ? $color[1] : 0;
			$b = isset($color[2]) && $color[2]>=0 && $color[2]<=255 ? $color[2] : 0;
			$a = isset($color[3]) && $color[3]>=0 && $color[3]<=255 ? $color[3] : 0;
		}

		// rgba value
		return array('r' => $r, 'g' => $g, 'b' => $b, 'a' => $a);
	}

	/**
	 * Converts a RGB color value to its HEX equivalent
	 * @param array $color
	 * @param bool $returnArray
	 * @return string
	 */
	public static function rgb2hex($color, $returnArray = false)
	{
		return self::rgba2hex($color, $returnArray);
	}

	/**
	 * Converts a RGBA color value to its HEX equivalent
	 * @param array $color
	 * @param bool $returnArray
	 * @return string
	 */
	public static function rgba2hex($color, $returnArray = false)
	{
		$hex = false;
		if ($color && is_array($color)) {
			$c = array();
			$alpha = null;

			if (isset($color['r'])) {
				$c[0] = isset($color['r']) ? $color['r'] : 0;
				$c[1] = isset($color['g']) ? $color['g'] : 0;
				$c[2] = isset($color['b']) ? $color['b'] : 0;
				if (isset($color['a'])) { $alpha = $color['a']; }
			} else {
				$c[0] = isset($color[0]) ? $color[0] : 0;
				$c[1] = isset($color[1]) ? $color[1] : 0;
				$c[2] = isset($color[2]) ? $color[2] : 0;
				if (isset($color[3])) { $alpha = $color[3]; }
			}
			
			$hex = sprintf("#%02x%02x%02x", $c[0], $c[1], $c[2]);
			if ($alpha !== null) {
				$hex .= self::percentage2hex($alpha);
			}
		}

		return $hex;
	}

	/**
	 * Reads the EXIF headers from an image file
	 * @param string $file The location of the image file. This can either be 
	 * a path to the file (stream wrappers are also supported as usual) 
	 * or a stream resource.
	 * @param string $requiredSections
	 * @param bool $thumbnail bool $thumbnail <p>When set to <b><code>TRUE</code></b> 
	 * the thumbnail itself is read. Otherwise, only the tagged data is read.</p>
	 * @return bool|array It returns an associative array where the array indexes 
	 * are the header names and the array values are the values associated with 
	 * those headers. If no data can be returned, exif_read_data() will return false.
	 */
	public function readExifData($file = null, $requiredSections = null, $thumbnail = false)
	{
		return $this->driver->readExifData($file, $requiredSections, $thumbnail);
	}

	/**
	 * Get image EXIF property
	 * @param string $property
	 * @param mixed $default
	 * @param Closure $callback
	 * @return mixed
	 */
	public function getExifData($property = null, $default = null, $callback = null)
	{
		return $this->driver->getExifData($property, $default, $callback);
	}

	/**
	 * Returns image date of creation
	 * @param string $format
	 * @return string|DateTime
	 */
	public function getDateCreated($format = null)
	{
		return $this->driver->getDateCreated($format);
	}
	
	/**
	 * Returns image date of creation
	 * @param string $format
	 * @return string|DateTime
	 */
	public function getGps($format = null)
	{
		return $this->driver->getGps($format);
	}
	
	
	
	
	
	
	
	
	
	

	
	
	
	
	
	








	





	/**
	 * Replace accented characters with non accented
	 * @param string $string
	 * @return string
	 */
	public function removeAccents($string)
	{
		$arr = array(
			'before' => array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή'),
			'after' => array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η')
		);

		return str_replace($arr['before'], $arr['after'], $string);
	}

	/**
	 * Set watermark configuration
	 * @param string $path
	 * @param array $config
	 * @return bool
	 */
	public function setWatermark($path, $config = array())
	{
		// already loaded
		if (self::$watermark !== null) {
			return self::$watermark!==false;
		}

		if ($path && !file_exists($path)) {
			self::$watermark = false;
			return false;
		}

		// load
		$cfg = is_array($config) ? $config : array();

		self::$watermark = array(
			'width' => 0,
			'height' => 0,
			'position' => (!empty($cfg['position']) ? $cfg['position'] : 'middle-bottom'),
			'offsetX' => (!empty($cfg['offsetX']) ? $cfg['offsetX'] : 0),
			'offsetY' => (!empty($cfg['offsetY']) ? $cfg['offsetY'] : -15),
			'image' => null,
		);

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
