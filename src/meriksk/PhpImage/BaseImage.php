<?php

namespace meriksk\PhpImage;
use Exception;

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
	public $source_info;
	public $orientation;

	protected $dataString;
	protected $dataBase64String;
	protected $original_path;
	protected $bg_color;


	protected static $driver = IMAGE::DRIVER_GD;
	
	abstract protected function loadImageFromFile($file);
	abstract protected function loadImageFromString();
	
	abstract protected function destroyResource($resource);
	abstract protected function pingImage($filename);
	abstract protected function saveImage($filename, $quality, $mimeType);
	abstract protected function outputImage($quality, $mimeType);
	abstract protected function resizeImage($width, $height);
	abstract protected function cropImage($x, $y, $width, $height);
	abstract protected function thumbnailImage($width, $height, $allowEnlarge);
	abstract protected function rotateImage($angle, $bgColor);
	abstract protected function flipImage($mode);

	
	////////////////////////////////////////////////////////////////////////////
	// Loaders
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Loads an image from a file.
	 * @param string $file
	 * @return $this
	 * @throws Exception
	 */
	public function fromFile($file)
	{
		$this->debug("fromFile()");
		$this->destroy();

		if (empty($file) || !is_string($file)) {
			throw new InvalidArgumentException('Invalid file data.');
		}

		$mimeType = null;
		$finfo = @finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo) {
			$mimeType = finfo_file($finfo, $file);

			if (strstr($mimeType, 'image') === false) {
				throw new Exception('Unsupported file type.');
			}
		} else {
			throw new \Exception("File not found. ($file)");
		}
		
		// store info
		$this->path = !empty($file) ? realpath($file) : null;
		$this->original_path = $this->path;
		$this->mime_type = $mimeType;

		// load an image resource
		$this->debug("loadImage()");
		$this->loadImageFromFile($file);
		$this->ping();

		// check image
		if (!$this->resource || !$this->mime_type) {
            throw new Exception('Could not read file');
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
	public function fromString($data, $driver = NULL)
	{
		$this->debug("fromString()");
		$this->destroy();

		$mimeType = null;
		$finfo = @finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo) {
			$mimeType = finfo_file($finfo, 'data://application/octet-stream;base64,' . base64_encode($data));
			if (strstr($mimeType, 'image') === false) {
				throw new Exception('Unsupported file type.');
			}
		} else {
			throw new \Exception("Failed to read image data. ($data)");
		}
		
		// store info
		$this->dataString = $data;
		$this->path = null;
		$this->original_path = $this->path;
		$this->mime_type = $mimeType;
		$data = null;

		$this->debug("loadImageFromString()");
		$this->loadImageFromString();
		$this->ping();
		
		// check image
		if (!$this->resource || !$this->mime_type) {
            throw new Exception('Could not read file');
        }

		return $this;
	}
	
	/**
	 * Fetch basic attributes about the image.
	 * @return array
	 * @throws Exception
	 */
	public function ping()
	{
		$this->debug("ping()");
		$this->debug("pingImage()");

		$result = $this->pingImage();
		if ($result) {
			return $this->getInfo();
		}
		
		// default
		return false;
	}

	/**
	 * Returns image info
	 * @return array
	 * @todo 'orientation'
	 */
	public function getInfo()
	{
		return [
			'path' => $this->path,
			'width' => $this->w,
			'height' => $this->h,
			'type' => $this->type,
			'mime_type' => $this->mime_type,
			'extension' => $this->extension,
			'orientation' => $this->orientation,
		];
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
		$this->path = null;
		$this->type = null;
		$this->mime_type = null;
		$this->extension = null;
		//$this->orientation = null;
		//$this->exifData = null;
		//$this->dateCreated = NULL;

		// working image resource
		$this->debug("destroyResource()");
		$this->destroyResource($this->resource);

		$this->source_info = null;
		$this->resource = null;
		$this->dataString = null;
		$this->dataBase64String = null;

		return $this;
	}

	/**
	 * Revert an image
	 * @return Image instance
	 */
	public function revert(): void
	{
		$this->debug("revert()\t\tcalling -> destroyResource()");
		$this->destroyResource($this->resource);
		$this->debug("revert()\t\tcalling -> load()");
		$this->load($this->original_path);
	}

	/**
	 * Outputs a message to the console.
	 * @param string $msg Message
	 * @param bool $newLine If true, new line character will sent to the output
	 * @param bool $inline
	 */
	protected function debug($msg, $newLine = TRUE, $inline = false): void
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
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $filename
	 * @param int $quality
	 * @param string $imageType
	 * @return bool
	 * @throws Exception
	 */
	public function save($filename = NULL, $quality = NULL, $imageType = NULL): bool
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

		return $this->saveImage($filename, $quality, $mimeType);
	}

	/**
	 * Outputs image without saving
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $imageType 
	 * @throws Exception
	 */
	public function output($quality = 100, $imageType = null)
	{
		$quality = $quality>0 && $quality<=100 ? $quality : 82;
		$mimeType = !empty($mimeType) ? $this->checkMimeType($imageType) : $this->mime_type;
		return $this->outputImage($quality, $mimeType);
	}
	
	public function toScreen($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		header('Content-Type: ' . $image['mime_type']);
		echo $image['data'];
		$image = null;
	}
	
	/**
	 * Generates an image string.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string 
	 */
	public function toString($quality = 100, $imageType = null) 
	{
		$image = $this->output($quality, $imageType);
		return $image['data'];
	}
	
	/**
	 * Encodes and returns image string with MIME base64
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string Returns a string containing a data URI.
	 */
	public function toBase64($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		return base64_encode($image['data']);
	}
	
	/**
	 * Generates a data URI.
	 * @param int $quality Image quality as a percentage (default 100).
	 * @param string $imageType The image format to output as a mime type (defaults to the original mime type).
	 * @return string Returns a string containing a data URI.
	 */
	public function toDataUri($quality = 100, $imageType = null)
	{
		$image = $this->output($quality, $imageType);
		return '<img src="data:'. base64_encode($image['mime_type']) .';base64,'. base64_encode($image['data']) .'" />';
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
	public function resize(int $width, int $height, bool $allowEnlarge = false): BaseImage
	{
        if ($allowEnlarge===false && $width>$this->w && $height>$this->h) {
            return $this;
        }
		
		// upscale check
		list ($w, $h) = $this->upscaleCheck($width, $height, $allowEnlarge);

		$this->debug("resize($width, $height, ". ($allowEnlarge===true ? "true":"false") .")");
		$this->debug("resizeImage($w, $h)");
		$this->resizeImage($w, $h);
		$this->ping();

		return $this;
	}

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param bool $allowEnlarge
     * @return BaseImage
     */
    public function resizeToWidth(int $width, bool $allowEnlarge = false): BaseImage
    {
        if ($allowEnlarge===false && $width>$this->w) {
            return $this;
        }

        $ratio  = $width / $this->w;
        $height = (int)round($this->h * $ratio);
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
    public function resizeToHeight(int $height, bool $allowEnlarge = false): BaseImage
    {
        if ($allowEnlarge===false && $height>$this->h) {
            return $this;
        }

        $ratio  = $height / $this->h;
        $width = (int)round($this->w * $ratio);
		$this->debug("resizeToWidth($height, ". ($allowEnlarge===true ? "true":"false") .")");
        $this->resize($width, $height, $allowEnlarge);
		return $this;
    }

    /**
     * Resizes image according to the given short side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @return static
     */
    public function resizeToShortSide($max, $allowEnlarge = false): void
    {
        if ($this->h < $this->w) {
            $ratio = $max / $this->h;
            $long = $this->w * $ratio;
            $this->resize($long, $max, $allowEnlarge);
        } else {
            $ratio = $max / $this->w;
            $long = $this->h * $ratio;
            $this->resize($max, $long, $allowEnlarge);
        }
    }

    /**
     * Resizes image according to the given long side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @return void
     */
    public function resizeToLongSide($max, $allowEnlarge = false): void
    {
        if ($this->h > $this->w) {
            $ratio = $max / $this->h;
            $short = $this->w * $ratio;
            $this->resize($short, $max, $allowEnlarge);
        } else {
            $ratio = $max / $this->w;
            $short = $this->h * $ratio;
            $this->resize($max, $short, $allowEnlarge);
        }
    }

    /**
     * Resizes image to best fit inside the given dimensions
     * @param int $width
     * @param int $height
     * @param bool $allowEnlarge
     * @return BaseImage
     */
    public function resizeToBestFit($width, $height, $allowEnlarge = false): BaseImage
    {
        if ($allowEnlarge===false && $width>$this->w && $height>$this->h) {
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
	public function crop($x, $y, $width, $height, $allowEnlarge = false): BaseImage
	{
		
		$x = $x>=0 && $x < $this->w ? $x : 0;
		$y = $y>=0 && $y < $this->h ? $y : 0;
		$w = $width;
		$h = $height;

		if ($x + $w > $this->w) { $w = $this->w - $x; }
		if ($y + $h > $this->h) { $h = $this->h - $y; }

		//var_dump(['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h]);die();
	
		$this->cropImage($x, $y, $w, $h);
		$this->ping();

		return $this;
	}

	/**
	 * Crops image according to the given width, height and crop position
	 * @param int $width
	 * @param int $height
	 * @param int $position
	 * @return BaseImage
	 */
	public function cropAuto($width, $height, $position = self::CROP_CENTER): BaseImage
	{

		$w = $width>0 && $width<=$this->w ? $width : $this->w;
		$h = $height>0 && $height<=$this->h ? $height : $this->h;

		if ($width < $this->w || $height < $this->h) {
			list ($x, $y) = $this->getCropPosition($width, $height, $this->w, $this->h, $position);
			$this->cropImage($x, $y, $w, $h);
			$this->ping();
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
	 * @return BaseImage
	 */
	public function thumbnail($width, $height, $allowEnlarge = false)
	{			
		$this->debug("thumbnail($width, $height, ". ($allowEnlarge===true ? "true":"false") .")");
		$this->debug("thumbnailImage($width, $height, ". ($allowEnlarge===true ? "true":"false") .")");
		$this->thumbnailImage($width, $height, $allowEnlarge);
		$this->ping();

		return $this;
	}

	/**
	 * Rotates an image.
	 * @param int $angle Rotation angle in degrees. Supports negative values.
	 * @param mixed $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Transparent by default.
	 * @return BaseImage
	 */
	public function rotate($angle, $bgColor = self::COLOR_TRANSPARENT)
	{
		$angle = (int)$angle;
		if (!is_numeric($angle) || $angle===0 || $angle<-359 || $angle > 359) {
			return $this;
		}

		$this->debug("rotate($angle)");
		$this->debug("rotateImage($angle)");
		$this->rotateImage($angle, $bgColor);
		$this->ping();
		
		return $this;
	}

	/**
	 * Flips an image using a given mode
	 * @param int|string $mode
	 * @return static
	 */
	public function flip($mode = Image::FLIP_VERTICAL)
	{
		if (in_array($mode, ['horizontal', 'vertical', 'both'])) {
			$this->debug("flip($mode) calling -> flipImage($mode)");
			$this->flipImage($mode);
			$this->ping();
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
			$rgba = $this->convertColor($color);
			$this->bg_color = $rgba;

			$this->debug("setBackgroundColor(". json_encode($color) .")");
			$this->debug("setImageBackgroundColor(". json_encode($rgba) .")");
			$this->setImageBackgroundColor($rgba);
		}
		return $this;
	}

	/**
	 * Converts a color value to its RGB equivalent
	 * @param string|array $color
	 * Where red, green, blue - ints 0-255, alpha - int 0-127
	 * @return array|bool
	 */
	protected function convertColor($color)
	{		
		// default color (transparent)
		$r = 255; $g = 255; $b = 255; $a = 127;
	
		if (is_numeric($color)) {
			switch ($color) {
				case Image::COLOR_BLACK:
					list ($r, $g, $b, $a) = [0, 0, 0, 0];
					break;
				case Image::COLOR_WHITE:
					list ($r, $g, $b, $a) = [255, 255, 255, 0];
					break;
				case Image::COLOR_TRANSPARENT:
				default:
					list ($r, $g, $b, $a) = [255, 255, 255, 127];
					break;
			}
		} elseif (is_string($color)) {
			$color = trim($color, '#');
			$r = '00';
			$g = '00';
			$b = '00';
			$a = 0;

			if (strlen($color) === 8) {
				list ($r, $g, $b, $a) = [hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]), hexdec($color[6].$color[7])];
			} elseif (strlen($color) === 6) {
				list ($r, $g, $b) = [hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5])];
			} elseif (strlen($color) === 3) {
				list ($r, $g, $b) = [hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2])];
			}

		} elseif (is_array($color)) {
			$r = isset($color[0]) && $color[0]>=0 && $color[0]<=255 ? $color[0] : 0;
			$g = isset($color[1]) && $color[1]>=0 && $color[1]<=255 ? $color[1] : 0;
			$b = isset($color[2]) && $color[2]>=0 && $color[2]<=255 ? $color[2] : 0;
			$a = 0;
		}

		return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a];
	}
	
	
	protected function rgba2Hex($color)
	{
		$hex = false;
		if ($color && is_array($color)) {
			$c = [];
			if (isset($color['r'])) {
				$c[0] = isset($color['r']) ? $color['r'] : 0;
				$c[1] = isset($color['g']) ? $color['g'] : 0;
				$c[2] = isset($color['b']) ? $color['b'] : 0;
				if (isset($color['a'])) { $c[3] = $color['a']; }
			} else {
				$c[0] = isset($color[0]) ? $color[0] : 0;
				$c[1] = isset($color[1]) ? $color[1] : 0;
				$c[2] = isset($color[2]) ? $color[2] : 0;
				if (isset($color[3])) { $c[3] = $color[3]; }
			}
			
			if (count($c)===4) {
				$hex = sprintf("#%02x%02x%02x%02x", $c[0], $c[1], $c[2], $c[3]);
			} else {
				$hex = sprintf("#%02x%02x%02x", $c[0], $c[1], $c[2]);
			}
		}
		
		return $hex;
	}

}