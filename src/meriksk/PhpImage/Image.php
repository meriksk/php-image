<?php

namespace meriksk\PhpImage;

use DateTime;
use DateTimeZone;
use Exception;
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
	 * @param string $lib
	 * @throws Exception
	 */
	public function __construct($filename = NULL, $lib = NULL)
	{
		// init driver
		$this->driver = DriverFactory::get($lib);

		// load image
		if ($filename !== null) {
			$this->driver->fromFile($filename);
		}
	}

	/**
	 * Load an image
	 * @param string $lib
	 * @return static
	 * @throws Exception
	 */
	public static function getInstance($lib = NULL)
	{
		return new Image(null, $lib);
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
	 * @return string
	 */
	public function getDriver()
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
	 * @throws \Exception Thrown if file or image data is invalid.
	 * @return \meriksk\PhpImage
	 */
	public function fromFile($file)
	{
		$this->driver->fromFile($file);
		return $this;
	}

	/**
	 * Creates a new image from a string.
	 * @param string $string The raw image data as a string.
	 * @example
	 *    $string = file_get_contents('image.jpg');
	 * @return Image
	 */
	public static function fromString($data, $lib = NULL)
	{
		$image = new Image(null, $lib);
		$image->driver->fromString($data);
		return $image;
	}

	/**
	 * Creates a new image from a base64 encoded string.
	 * @param string $string The raw image data encoded as a base64 string.
	 * @return Image
	 */
	public static function fromBase64String($data, $lib = NULL)
	{
		$image = new Image(null, $lib);
		$image->driver->fromString(base64_decode($data));
		return $image;
	}

	/**
	 * Fetch basic attributes about the image.
	 * @return array
	 * @throws Exception
	 */
	public function ping()
	{
		return $this->driver->ping();
	}

	/**
	 * Get image info
	 * @return array
	 */
	public function getInfo()
	{
		return $this->driver->getInfo();
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
	 * Returns image dimensions
	 * @return array
	 */
	public function getDimensions()
	{
		return [$this->driver->w, $this->driver->h];
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
	 * @param bool $withDot
	 * @return string
	 */
	public function getExtension($withDot = false)
	{
		return $this->driver->extension ? ($withDot===true ? '.' : '') . $this->driver->extension : null;
	}
	
	/**
	 * Get image orientation
	 * @param NULL|int $width
	 * @param NULL|int $height
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
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $filename If omitted - original file will be overwritten
	 * @param int $quality	Output image quality in percents 0-100
	 * @param string $imageType The image type to use; determined by file extension if null
	 * @return bool
	 * @throws Exception
	 */
	public function save($filename = NULL, $quality = NULL, $imageType = NULL)
	{
		return $this->driver->save($filename, $quality, $imageType);
	}
	
	/**
	 * Outputs the image to the screen. Must be called before any output is sent to the screen.
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType If omitted or null - image type of original file will be used (extension or mime-type)
	 */
	public function toScreen($quality = NULL, $imageType = NULL)
	{
		$this->driver->toScreen($quality, $imageType);
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
	 * Resize an image to the specified dimensions
	 * @param int|array $width Desired width - number or array [w, h]
	 * @param int|null $height Desired height, if omitted - assumed equal to $width
	 * @param bool $allowEnlarge
	 * @return static
	 */
	public function resize($width, $height, bool $allowEnlarge = false)
	{
		$this->driver->resize($width, $height, $allowEnlarge);
		return $this;
	}

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToWidth($width, bool $allowEnlarge = false)
    {
		$this->driver->resizeToWidth($width, $allowEnlarge);
        return $this;
    }

    /**
     * Resizes image according to the given height (width proportional)
     * @param int $height
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToHeight($height, bool $allowEnlarge = false)
    {
		$this->driver->resizeToHeight($height, $allowEnlarge);
        return $this;
    }

    /**
     * Resizes image according to the given short side (long side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToShortSide($max, $allowEnlarge = false)
    {
        $this->driver->resizeToShortSide($max, $allowEnlarge);
        return $this;
    }

    /**
     * Resizes image according to the given long side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToLongSide($max, $allowEnlarge = false)
    {
        $this->driver->resizeToLongSide($max, $allowEnlarge);
        return $this;
    }

    /**
     * Resizes image to best fit inside the given dimensions
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge = false)
    {
        $this->driver->resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge);
		return $this;
    }

	/**
	 * Crops image according to the given coordinates.
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @return static
	 */
	public function crop($x, $y, $width, $height, $allowEnlarge = false)
	{
		$this->driver->crop($x, $y, $width, $height, $allowEnlarge);
		return $this;
	}

	/**
	 * Crops image according to the given width, height and crop position.
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @param int $position
	 * @return static
	 */
	public function cropAuto($width, $height, $position = self::CROP_CENTER)
	{
		$this->driver->cropAuto($width, $height, $position);
		return $this;
	}

	/**
	 * Extracts a region of the image.
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @return static
	 * @throws Exception
	 */
	public function thumbnail($width, $height, $allowEnlarge = false)
	{
		// check desired dimensions
		if (!is_numeric($width) || $width < 0) {
			throw new InvalidArgumentException("Width must be a valid int.");
		}
		if (!is_numeric($height) || $height < 0) {
			throw new InvalidArgumentException("Height must be a valid int.");
		}

		$this->driver->thumbnail($width, $height, $allowEnlarge);
		return $this;
	}

	/**
	 * Rotates an image.
	 * @param int $angle Rotation angle in degrees. Supports negative values.
	 * @param mixed $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Transparent by default.
	 * @return static
	 */
	public function rotate($angle, $bgColor = self::COLOR_TRANSPARENT)
	{
		if (!is_numeric($angle) || $angle<-360 || $angle>360) {
			throw new InvalidArgumentException("Width must be a valid int.");
		}

		$this->driver->rotate($angle, $bgColor);
		return $this;
	}

	/**
	 * Flips an image using a given mode
	 * @param int|string $mode
	 * @return static
	 */
	public function flip($mode = self::FLIP_VERTICAL)
	{
		$this->driver->flip($mode);
		return $this;
	}

	/**
	 * Set background color
	 * @param string|array $color
	 * @return $this
	 */
	public function setBackgroundColor($color)
	{
		$this->driver->setBackgroundColor($color);
		return $this;
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
	 * Opposite color
	 * @param string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - ints 0-255, alpha - int 0-127
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
