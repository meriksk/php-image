<?php

namespace meriksk\PhpImage;
use Exception;
use InvalidArgumentException;

abstract class BaseImage
{

	// working image
	public $resource;
	public $path;
	public $image_string;
	public $w;
	public $h;
	public $type;
	public $mime_type;
	public $extension;
	public $orientation;
	public $exif;

	protected $dataBase64String;
	protected $bg_color;


	protected static $driver = IMAGE::DRIVER_GD;

	abstract protected function _loadFromFile($file);

	abstract protected function _ping($filename);
	abstract protected function _destroy($resource);
	abstract protected function _save($filename, $quality, $mimeType);
	abstract protected function _output($quality, $mimeType);
	abstract protected function _resize($width, $height);
	abstract protected function _crop($x, $y, $width, $height);
	abstract protected function _thumbnail($width, $height, $fill, $allowEnlarge);
	abstract protected function _flip($mode);
	abstract protected function _rotate($angle, $bgColor);
	abstract protected function _setBackgroundColor($color);


	////////////////////////////////////////////////////////////////////////////
	// Loaders
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Loads an image from a file.
	 * @param string $filename
	 * @return $this
	 * @throws Exception
	 */
	public function loadFromFile($filename)
	{
		$this->debug("loadFromFile()");
		$this->destroy();

		if (empty($filename) || !is_string($filename)) {
			throw new InvalidArgumentException('Invalid file data.');
		}

		$handle = @fopen($filename, 'r');
		if (!$handle) {
			throw new Exception('File not found. ('. $filename . ')');
		} fclose($handle);

		// store info
		if (stripos($filename, 'http')===false) {
			$this->path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($filename));
		} else {
			$this->path = trim($filename);
		}

		// ping image
		$this->_ping($this->path);

		// load image resource
		$this->debug("loadImage()");
		$this->_loadFromFile($filename);

		// check image
		if (!$this->resource || !$this->mime_type) {
            throw new Exception('Could not read file.');
        }

		return $this;
	}
	
	/**
	 * Creates a new image from a string.
	 * @param string $string The raw image data as a string.
	 * @example
	 *    $string = file_get_contents('image.jpg');
	 * @return Image
	 */
	public function loadFromString($data, $encode = true)
	{
		$this->debug("loadFromString()");
		$this->destroy();

		// convert to base64
		if ($encode === true) {
			$data = base64_encode((string)$data);
		} else {
			$data = (string)$data;
		}

		// data stream
		$data = 'data://application/octet-stream;base64,' . $data;

		// ping image
		$this->_ping($data);

		// load image resource
		$this->debug("_loadFromString()");
		$this->_loadFromFile($data);

		// check image
		if (!$this->resource || !$this->mime_type) {
            throw new Exception('Could not read file.');
        }

		return $this;
	}

	/**
	 * Creates a new image from a string.
	 * @param string $string The raw image data as a string (base64).
	 * @return Image
	 */
	public function loadFromBase64String($data)
	{
		$this->debug("loadFromBase64String()");
		return $this->loadFromString($data, false);
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
		// check image
		if (!empty($filename)) {
			if (!file_exists($filename)) {
				throw new \Exception("File not found. ($filename)");
			}

			// store info
			$this->path = $filename;
		}

		$this->debug("ping()");
		$this->debug("_ping()");
		$this->_ping($filename);

		return $this->getInfo();
	}

	/**
	 * Get image info
	 * @param bool $extendedInfo Read extended information about the image (Exif, Gps, ...)
	 * @return array
	 */
	public function getInfo($extendedInfo = false)
	{
		$info = [];
		if (!empty($this->mime_type)) {
			$info = [
				'path' => $this->path,
				'width' => $this->w,
				'height' => $this->h,
				'type' => $this->type,
				'mime_type' => $this->mime_type,
				'extension' => $this->extension,
				'orientation' => $this->orientation,
			];
			
			if ($extendedInfo === true) {
				$info['exif'] = $this->readExifData($this->path);
			}
		}
		
		return $info;
	}

	/**
	 * Destroy image resources
	 * @param resource $image
	 * @return Image instance
	 */
	public function destroy()
	{
		$this->debug("destroy()");

		$this->w = null;
		$this->h = null;
		$this->type = null;
		$this->mime_type = null;
		$this->extension = null;
		$this->orientation = null;
		$this->bg_color = null;
		$this->exifData = null;

		//$this->dateCreated = NULL;

		// working image resource
		$this->debug("destroyResource()");
		$this->_destroy($this->resource);

		$this->resource = null;
		//$this->dataBase64String = null;

		return $this;
	}

	/**
	 * Outputs a message to the console.
	 * @param string $msg Message
	 * @param bool $newLine If true, new line character will sent to the output
	 * @param bool $inline
	 */
	protected function debug($msg, $newLine = TRUE, $inline = false)
	{
		if (Image::$debug === true && php_sapi_name() === 'cli') {
			echo ($inline ? '' : "--->\t") . $msg . ($newLine ? "\n\r" : '');
		}
	}

	/**
	 * Is an image a valid image resource?
	 * @param resource $resource
	 * @return bool
	 */
	protected function isResource($resource = NULL): bool
	{
		if ($resource === NULL) {
			$resource = $this->resource;
		}

		if (
			$resource
			&&
			(
				(is_resource($resource) && 'gd'===get_resource_type($resource))
				||
				is_object($resource) && 'Imagick'===get_class($resource)
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns image dimensions
	 * @return array Returns image dimensions or <b>FALSE</b> on error
	 */
	public function getDimensions()
	{
		if ($this->w>0 && $this->h>0) {
			return [$this->w, $this->h];
		}

		return false;
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
		$filename = !empty($filename) ? $filename : $this->path;
		$quality = is_numeric($quality) && $quality>0 && $quality<=100 ? (int)$quality : 75;
		$mimeType = $imageType ?: $this->mime_type;

        if (empty($filename) || $filename === null) {
            throw new Exception('Filename must not be empty.');
        }

		// check mime type
		$mimeType = $this->checkMimeType($mimeType);

		$this->debug("save(\"". basename($filename) ."\", ". (!is_null($quality) ? $quality : "null").", ". (!is_null($imageType) ? "\"$imageType\"" : "null") . ")");
		$this->debug("saveImage(\"$filename\", $quality, \"$mimeType\")");

		return $this->_save($filename, $quality, $mimeType);
	}

	/**
	 * Revert an image
	 * @return BaseImage
	 */
	public function revert()
	{
		$this->destroy();
		$this->loadFromFile($this->path);
		return $this;
	}
	
	/**
	 * Outputs image without saving
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType
	 * @return array
	 * @throws Exception
	 */
	private function output($quality = 100, $imageType = null)
	{
		$quality = $quality>0 && $quality<=100 ? $quality : 82;
		$mimeType = !empty($imageType) ? $this->checkMimeType($imageType) : $this->mime_type;

		return $this->_output($quality, $mimeType);
	}

	/**
	 * Generates an image string.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string image string or <b>FALSE</b> on error
	 */
	public function toString($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		return $image ? $image['data'] : false;
	}

	/**
	 * Encodes and returns image string with MIME base64
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string Returns a string containing a data URI or <b>FALSE</b> on error
	 */
	public function toBase64($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		return $image ? base64_encode($image['data']) : false;
	}

	/**
	 * Generates a data URI.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string Returns a string containing a data URI or <b>FALSE</b> on error
	 */
	public function toDataUri($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		return $image ? 'data:'. base64_encode($image['mime_type']) .';base64,'. base64_encode($image['data']) : false;
	}

	/**
	 * Outputs the image to the screen. Must be called before any output is sent to the screen.
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType If omitted or null - image type of original file will be used (extension or mime-type)
	 * @return void
	 */
	public function toScreen($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		if ($image) {
			header('Content-Type: ' . $image['mime_type']);
			echo $image['data'];
		}

		$image = null;
	}

	/**
	 * Check mime type
	 * @param string $mimeType
	 * @throws Exception
	 */
	protected function checkMimeType($mimeType)
	{
		switch ($mimeType) {
			case 'gif':
			case 'image/gif':
			case IMAGETYPE_GIF:
				return 'image/gif';
			case 'png':
			case 'image/png':
			case IMAGETYPE_PNG:
				return 'image/png';
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
			case 'image/x-jpeg':
			case IMAGETYPE_JPEG:
				return 'image/jpeg';
			default:
				throw new Exception('Unsupported format');
		}
	}

	/**
	 * Upscale check
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @return array
	 */
	protected function upscaleCheck($width, $height, $allowEnlarge = NULL)
	{
		if ($allowEnlarge === true) {
			$w = $width;
			$h = $height;
		} else {
			$w = $width <= $this->w ? $width : $this->w;
			$h = $height <= $this->h ? $height : $this->h;
		}

		return [$w, $h];
	}

	/**
	 * Returns image orientation
	 * @return string
	 */
	public function getOrientation()
	{
		if ($this->w > $this->h) {
			return Image::ORIENTATION_LANDSCAPE;
		} elseif ($this->h > $this->w) {
			return Image::ORIENTATION_PORTRAIT;
		} elseif ($this->w == $this->h) {
			return Image::ORIENTATION_SQUARE;
		} else {
			return false;
		}
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width Desired width
	 * @param int $height Desired height
	 * @param bool $allowEnlarge
	 * @return BaseImage
	 */
	public function resize($width, $height, $allowEnlarge = true)
	{
		
		if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
			throw InvalidArgumentException('Width and height value must be an integer and must be non negative.');
		}
		
		// enlarge disabled
        if ($allowEnlarge===false && ($width > $this->w || $height > $this->h)) {
            return $this;
        }

		// upscale check
		list ($w, $h) = $this->upscaleCheck($width, $height, $allowEnlarge);

		$this->debug("resize($width, $height, ". ($allowEnlarge===true ? "true":"false") .")");
		$this->debug("_resize($w, $h)");
		$this->_resize($w, $h);
		$this->_ping();

		return $this;
	}

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param bool $allowEnlarge
     * @return BaseImage
     */
    public function resizeToWidth($width, $allowEnlarge = true)
    {
		if (!is_numeric($width) || $width <= 0) {
			throw InvalidArgumentException('Width must be an integer and must be non negative.');
		}

        $ratio  = $width / $this->w;
        $height = (int)round($this->h * $ratio);

		// enlarge disabled
        if ($allowEnlarge===false && ($width > $this->w || $height > $this->h)) {
            return $this;
        }

		$this->debug("resizeToWidth($width, ". ($allowEnlarge===true ? "true":"false") .")");
        $this->resize($width, $height, $allowEnlarge);
		return $this;
    }

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $height
     * @param bool $allowEnlarge
     * @return BaseImage
     */
    public function resizeToHeight($height, $allowEnlarge = true)
    {
		if (!is_numeric($height) || $height <= 0) {
			throw InvalidArgumentException('Height must be an integer and must be non negative.');
		}

        $ratio  = $height / $this->h;
        $width = (int)round($this->w * $ratio);

		// enlarge disabled
        if ($allowEnlarge===false && ($width > $this->w || $height > $this->h)) {
            return $this;
        }

		$this->debug("resizeToHeight($height, ". ($allowEnlarge===true ? "true":"false") .")");
        $this->resize($width, $height, $allowEnlarge);
		return $this;
    }

    /**
     * Resizes image according to the given short side (short side proportional)
     * @param int $maxShort
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToShortSide($maxShort, $allowEnlarge = true)
    {
		
		if (!is_numeric($maxShort) || $maxShort <= 0) {
			throw InvalidArgumentException('Size must an integer and must be non negative.');
		}

        if ($this->h < $this->w) {
            $ratio = $maxShort / $this->h;
            $long = $this->w * $ratio;
            $this->resize($long, $maxShort, $allowEnlarge);
        } else {
            $ratio = $maxShort / $this->w;
            $long = $this->h * $ratio;
            $this->resize($maxShort, $long, $allowEnlarge);
        }
    }

    /**
     * Resizes image according to the given long side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @return void
     */
    public function resizeToLongSide($maxLong, $allowEnlarge = true)
    {

		if (!is_numeric($maxLong) || $maxLong <= 0) {
			throw InvalidArgumentException('Size must an integer and must be non negative.');
		}

        if ($this->h > $this->w) {
            $ratio = $maxLong / $this->h;
            $short = $this->w * $ratio;
            $this->resize($short, $maxLong, $allowEnlarge);
        } else {
            $ratio = $maxLong / $this->w;
            $short = $this->h * $ratio;
            $this->resize($maxLong, $short, $allowEnlarge);
        }
    }

    /**
     * Resizes image to best fit inside the given dimensions
     * @param int $width
     * @param int $height
     * @param bool $allowEnlarge
     * @return BaseImage
     */
    public function resizeToBestFit($width, $height, $allowEnlarge = true)
    {
		
		if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
			throw InvalidArgumentException('Width and height value must be an integer and must be non negative.');
		}

		// enlarge disabled
        if ($allowEnlarge===false && ($width > $this->w || $height > $this->h)) {
            return $this;
        }

		list ($w, $h) = $this->upscaleCheck($width, $height, $allowEnlarge);

        $srcRatio = $this->w / $this->h;
        $dstRatio = $w / $h;

		$this->debug("resizeToBestFit($width, $height, ". ($allowEnlarge===true ? "true":"false") .")");

		if ($srcRatio > $dstRatio) {
			$this->resizeToWidth($w, $allowEnlarge);
		} else {
			$this->resizeToHeight($h, $allowEnlarge);
		}

		return $this;
    }

	/**
	 * Crops image according to the given coordinates
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @return BaseImage
	 */
	public function crop($x, $y, $width, $height, $allowEnlarge = false)
	{

		if (!is_numeric($x) || !is_numeric($y) || $x < 0 || $y < 0) {
			throw new InvalidArgumentException('X and Y coordinate must be an integer and must be non negative.');
		}
		
		if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
			throw new InvalidArgumentException('Width and height value must be an integer and must be non negative.');
		}
		
		$w = (int)round($width);
		$h = (int)round($height);
		
		// enlarge disabled
        if ($allowEnlarge===false) {
			if ($x + $w > $this->w) { $w = $this->w - $x; }
			if ($y + $h > $this->h) { $h = $this->h - $y; }	
        }

		$this->_crop($x, $y, $w, $h);
		$this->_ping();

		return $this;
	}

	/**
	 * Crops image according to the given width, height and crop position
	 * @param int $width
	 * @param int $height
	 * @param int $position
	 * @return BaseImage
	 */
	public function cropAuto($width, $height, $position = self::CROP_CENTER)
	{

		if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
			throw new InvalidArgumentException('Width and height value must be an integer and must be non negative.');
		}
		
		$w = $width>0 && $width<=$this->w ? $width : $this->w;
		$h = $height>0 && $height<=$this->h ? $height : $this->h;

		if ($width < $this->w || $height < $this->h) {
			list ($x, $y) = $this->getCropPosition($width, $height, $this->w, $this->h, $position);
			$this->_crop($x, $y, $w, $h);
			$this->_ping();
		}

		return $this;
	}

    /**
     * Gets crop position (X or Y) according to the given position
     * @param int $width
     * @param int $height
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @param int $mode
     * @return array
     */
    protected function getCropPosition($width, $height, $sourceWidth, $sourceHeight, $mode = Image::CROP_CENTER)
    {
        $x = 0;
		$y = 0;

		if ($width < $sourceWidth || $height < $sourceHeight) {
			switch ($mode) {
				case Image::CROP_LEFT:
					$x = 0;
					$y = ($sourceHeight - $height)/2;
					break;
				case Image::CROP_RIGHT:
					$x = $sourceWidth - $width;
					$y = ($sourceHeight - $height)/2;
					break;
				case Image::CROP_TOP:
					$x = ($sourceWidth - $width)/2;
					$y = 0;
					break;
				case Image::CROP_BOTTOM:
					$x = ($sourceWidth - $width)/2;
					$y = $sourceHeight - $height;
					break;
				case Image::CROP_TOP_LEFT:
					$x = 0;
					$y = 0;
					break;
				case Image::CROP_TOP_RIGHT:
					$x = $sourceWidth - $width;
					$y = 0;
					break;
				case Image::CROP_BOTTOM_LEFT:
					$x = 0;
					$y = $sourceHeight - $height;
					break;
				case Image::CROP_BOTTOM_RIGHT:
					$x = $sourceWidth - $width;
					$y = $sourceHeight - $height;
					break;
				case Image::CROP_CENTER:
				default:
					$x = ($sourceWidth - $width)/2;
					$y = ($sourceHeight - $height)/2;
					break;
			}

			$x = $x>=0 ? $x : 0;
			$y = $y>=0 ? $y : 0;
		}

        return [$x, $y];
    }

	/**
	 * Thumbnail an image
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 * @param bool $fill
	 * @return BaseImage
	 */
	public function thumbnail($width, $height, $fill = false, $allowEnlarge = false)
	{
		$this->debug("thumbnail($width, $height, ". ($fill===true ? 'true':'false').", ". ($allowEnlarge===true ? 'true':'false') .")");
		$this->debug("_thumbnail($width, $height, ". ($fill===true ? 'true':'false').", ". ($allowEnlarge===true ? 'true':'false') .")");
		$this->_thumbnail($width, $height, $fill, $allowEnlarge);
		$this->_ping();

		return $this;
	}
	
	/**
	 * Flips an image using a given mode
	 * @param int|string $mode
	 * @return static
	 */
	public function flip($mode = Image::FLIP_VERTICAL)
	{
		$this->debug("flip($mode)");		
		$this->debug("_flip($mode)");
		$this->_flip($mode);
		$this->_ping();		

		return $this;
	}

	/**
	 * Rotate an image with a given angle and background color
	 * @param float $angle <p>Rotation angle, in degrees. The rotation angle is 
	 * interpreted as the number of degrees to rotate the image anticlockwise.</p>
	 * @param string|array $bgd_color <p>Specifies the color of the uncovered 
	 * zone after the rotation</p> Transparent by default.
	 * @return void
	 */
	public function rotate($angle, $bgColor = self::COLOR_TRANSPARENT)
	{
		$angle = (int)$angle;
		if (!is_numeric($angle) || $angle===0 || $angle<-359 || $angle > 359) {
			return $this;
		}

		$this->debug("rotate($angle)");
		$this->debug("rotateImage($angle)");
		$this->_rotate($angle, $bgColor);
		$this->_ping();

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
	 * @param string|array $color
	 * @return $this
	 */
	public function setBackgroundColor($color)
	{
		if (is_string($color) || is_array($color)) {
			$rgba = Image::normalizeColor($color);
			$this->bg_color = $rgba;

			$this->debug("setBackgroundColor(". json_encode($color) .")");
			$this->debug("setImageBackgroundColor(". json_encode($rgba) .")");
			$this->_setBackgroundColor($rgba);
		}
		return $this;
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
		$file = !empty($file) ? $file : $this->path;
		$this->debug('readExifData("'. $file . '")');
		$exif = $this->_readExifData($file, $requiredSections, $thumbnail);
		$this->exif = !empty($exif) ? $exif : false;
		return $this->exif;
	}

	/**
	 * Gets the EXIF headers from an image file
	 * @param string $property
	 * @param mixed $default
	 * @param Closure $callback
	 * @return mixed It returns an exif property value. If no EXIF data can be returned, 
	 * function will return false.
	 */
	public function getExifData($property = null, $default = null, $callback = null)
	{
	
		$d = !empty($property) ? (is_array($property) ? json_encode($property) : '"'. (string)$property .'"') : '';
		$this->debug('getExifProperty('. $d .')');
		
		// exif data
		if ($this->exif === null) {
			$this->readExifData();
		}

		if ($this->exif === false) {
			return false;
		}
		
		// return all EXIF data
		if ($property===null) {
			return $this->exif;
		}

		$value = $default;
		
		// props 
		$props = [];
		$allowCallback = true;

		if (is_array($property)) {
			$props = $property;
			$allowCallback = false;
		} elseif (is_string($property)) {
			$props = [$property];
		}
		
		// named properties
		$namedProperties = [
			'model' => 'Model',
			'description' => 'ImageDescription',
			'date_created' => 'DateTimeOriginal',
			'orientation' => 'Orientation',
			'exposure' => 'ExposureTime',
			'f' => 'FNumber',
			'f_number' => 'FNumber',
			'aperture ' => 'FNumber',
			'shutter_speed' => 'ExposureTime',
			'iso' => 'ISOSpeedRatings',
			'flash' => 'Flash',
			'focal_length' => 'FocalLength',
			'comment' => 'UserComment',
		];

		$value = [];
		foreach ($props as $prop) {
			if (isset($namedProperties[$prop])) {
				$prop = $namedProperties[$prop];
			}
			
			$val = null;

			// read data
			if (isset($this->exif[$prop])) {
				$val = $this->exif[$prop];
			} elseif (isset($this->exif['exif:' . $prop])) {
				$val = $this->exif['exif:' . $prop];
			}

			// custom callback
			if ($allowCallback) {
				if ($callback && $callback instanceof \Closure) {
					$val = call_user_func_array($callback, [$val, $this->exif]);
				}
			}
			
			$value[$prop] = $val;
		}

		return count($value)>1 ? $value : current($value);
	}
	
	/**
	 * Returns image date of creation
	 * @param string $format
	 * @return string|DateTime
	 */
	public function getDateCreated($format = null)
	{
		return $this->getExifData('date_created', null, function($val) use ($format) {
			$dt = \DateTime::createFromFormat('Y:m:d H:i:s', $val, new \DateTimeZone('UTC'));
			if ($dt) {
				return is_string($format) ? $dt->format($format) : $dt;
			} else {
				return false;
			}
		});
	}
	
	/**
	 * Returns image date of creation
	 * @param bool $dmsFormat
	 * @return string|DateTime
	 */
	public function getGps($dmsFormat = null)
	{
		
		// get lat and lng (format: [coord, hemisphere])
		$lat = $this->getExifData(['GPSLatitude', 'GPSLatitudeRef']);
		$lng = $this->getExifData(['GPSLongitude', 'GPSLongitudeRef']);
		
		if (empty($lat) || empty($lng)) {
			return null;
		}
		
		$gps = [];
		foreach([$lat, $lng] as $index => $coordinate) {
			/* @var $coordinate array */

			if (!is_array($coordinate)) {
				return null;
			}

			// reset keys [0: coord, 1: hemisphere]
			$values = array_values($coordinate);
			
			if (!isset($values[0]) || !isset($values[1])) {
				return null;
			}
			
			$coord = isset($values[0]) ? $values[0] : null;
			$hemisphere = isset($values[1]) ? $values[1] : null;
			
			if (is_string($coord)) {
				$coord = array_map('trim', explode(',', $coord));
			}

			$count = count($coord);
			$degrees = $count > 0 ? $this->gps2Num($coord[0]) : 0;
			$minutes = $count > 1 ? $this->gps2Num($coord[1]) : 0;
			$seconds = $count > 2 ? $this->gps2Num($coord[2]) : 0;

			$sign = ($hemisphere === 'W' || $hemisphere === 'S') ? -1 : 1;
			$key = $index===0 ? 'lat' : 'lng';
			
			if ($dmsFormat === true) {

				//normalize
				$minutes += 60 * ($degrees - floor($degrees));
				$degrees = floor($degrees);

				$seconds += 60 * ($minutes - floor($minutes));
				$minutes = floor($minutes);

				//extra normalization, probably not necessary unless you get weird data
				if($seconds >= 60) {
					$minutes += floor($seconds/60.0);
					$seconds -= 60*floor($seconds/60.0);
				}

				if($minutes >= 60) {
					$degrees += floor($minutes/60.0);
					$minutes -= 60*floor($minutes/60.0);
				}
				
				$gps[$key] = [
					'degrees' => $degrees,
					'minutes' => $minutes, 
					'seconds' => $seconds
				];
				
			} else {
				
				$gps[$key] = $sign * ($degrees + $minutes/60 + $seconds/3600);
			}
		}//foreach
	
		return !empty($gps) ? $gps : null;
	}

	private function gps2Num($coordPart)
	{
		$parts = explode('/', $coordPart);
		$count = count($parts);

		if ($count <= 0)
			return 0;

		if ($count == 1)
			return $parts[0];

		return floatval($parts[0]) / floatval($parts[1]);
	}
	
}