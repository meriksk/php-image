<?php

namespace meriksk\Image;


abstract class BaseImage
{

	// working image
	public $resource;
	public $path;
	public $w;
	public $h;
	public $type;
	public $mime_type;
	public $extension;
	public $source_info;

	// original image
	protected $original_path;

	protected static $debug = false;

	//abstract public function readImageData(): array;
	abstract protected function loadImage($filename): void;
	abstract protected function destroyResource($resource): void;
	abstract protected function pingImage($image): void;
	abstract protected function saveImage($filename, $quality, $mimeType): bool;
	abstract protected function outputImage($mimeType, $quality): void;
	abstract protected function resizeImage($width, $height): void;
	abstract protected function cropImage($startX, $startY, $width, $height): void;
	abstract protected function rotateImage($angle, $bgColor): void;
	abstract protected function flipImage($mode): void;

	/**
	 * Load an image
	 * @param string $filename
	 * @return $this
	 * @throws Exception
	 */
	public function load($filename)
	{

		if (!empty($filename) && substr($filename, 0, 5) !== 'data:' && !is_file($filename)) {
			throw new \Exception('File does not exist');
        }

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (strstr(finfo_file($finfo, $filename), 'image') === false) {
            throw new \Exception('Unsupported file type');
        }

		// collect image info
		$this->ping($filename);

		// check image
		if (!$this->mime_type) {
            throw new \Exception('Could not read file');
        }

		// load an image resource
		$this->loadImage($filename);

		// check image
		if (!$this->resource) {
            throw new \Exception('Could not read file');
        }

		// store info
		$this->path = $filename;
		$this->original_path = $filename;

		return $this;
	}

	public function loadFromString($data)
	{

        if (empty($data) || $data === null) {
            throw new Exception('Image data must not be empty.');
        }

		if (!is_string($data)) {
			throw new Exception('Image data must be string.');
		}

		$this->load('data://application/octet-stream;base64,' . base64_encode($data));

		return $this;
	}

	public function loadFromBase64String($data)
	{

        if (empty($data) || $data === null) {
            throw new Exception('Image data must not be empty.');
        }

		if (!is_string($data)) {
			throw new Exception('Image data must be string.');
		}

		$this->load('data://application/octet-stream;base64,' . $data);

		return $this;
	}

	/**
	 * Ping an image (fetch basic attributes about the image)
	 * @param string $filename Filename or image data string
	 * @return array
	 * @throws Exception
	 */
	public function ping($filename = NULL): array
	{
		$this->debug("ping()\t\t\tcalling -> pingImage(..". basename($filename) . ")");
		$this->pingImage($filename);

		return $this->getInfo();
	}

	/**
	 * Returns image info
	 * @return array
	 * @todo 'orientation'
	 */
	public function getInfo(): array
	{
		return [
			'path' => $this->path,
			'width' => $this->w,
			'height' => $this->h,
			'type' => $this->type,
			'mime_type' => $this->mime_type,
			'extension' => $this->extension,
		];
	}

	/**
	 * Destroy image resources
	 * @param resource $image
	 * @return Image instance
	 */
	public function destroy()
	{
		$this->debug("destroy()\t\tcalling -> destroyResource()");

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
		$this->destroyResource($this->resource);

		$this->source_info = null;
		$this->resource = NULL;

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
		if (self::$debug === true) {
			consoleLog($msg, $newLine, $inline);
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

		$this->debug("save()\t\t\tquality: $quality, mime-type: $imageType, filename: .." . basename($filename));
		$this->debug("save()\t\t\tcalling saveImage()");

		return $this->saveImage($filename, $quality, $mimeType);
	}

	/**
	 * Outputs image without saving
	 * @param string $imageType 
	 * @param int $quality Output image quality in percents 0-100
	 * @throws Exception
	 */
	public function output($imageType = NULL, $quality = NULL)
	{		
		// mime-type
		$mimeType = !empty($imageType) ? $imageType : $this->mime_type;
		
		header('Content-Type: ' . $mimeType);
		$this->outputImage($mimeType, $quality);
		$this->destroy();
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
			case IMAGETYPE_JPEG:
				return 'image/jpeg';
			default:
				throw new \Exception('Unsupported format');
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
		// if upscale allowed or if it already fits, there's nothing to do
		if ($allowEnlarge === true || ($width <= $this->w && $height <= $this->h)) {
			return [$width, $height];
		}

		// check width
		if ($width > $this->w) {
			$width = $this->w;
		}

		// check height
		if ($height > $this->h) {
			$height = $this->h;
		}

		return [$width, $height];
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width Desired width
	 * @param int $height Desired height
	 * @param bool $allowEnlarge
	 * @return Image instance
	 */
	public function resize(int $width, int $height, bool $allowEnlarge = false): void
	{
		$w = $width > 0 ? $width : $this->w;
		$h = $height > 0 ? $height : $this->h;

		// upscale check
		list ($w, $h) = $this->upscaleCheck($w, $h, $allowEnlarge);

		$this->debug("resize($width, $height)\tcalling -> resizeImage($w, $h)");
		$this->resizeImage($w, $h);
		$this->ping();
	}

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param boolean $allowEnlarge
     * @return static
     */
    public function resizeToWidth(int $width, bool $allowEnlarge = false): void
    {
        $ratio  = $width / $this->w;
        $height = (int)round($this->h * $ratio);
        $this->resize($width, $height, $allowEnlarge);
    }

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $height
     * @param boolean $allowEnlarge
     * @return static
     */
    public function resizeToHeight(int $height, bool $allowEnlarge = false): void
    {
        $ratio  = $height / $this->h;
        $width = (int)round($this->w * $ratio);
        $this->resize($width, $height, $allowEnlarge);
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
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
     * @return void
     */
    public function resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge = false): void
    {
        if ($this->w <= $maxWidth && $this->h <= $maxHeight && $allowEnlarge === false) {
            return;
        }

        $ratio = $this->h / $this->w;
        $width = $maxWidth;
        $height = round($width * $ratio);

		if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = $height / $ratio;
        }

        $this->resize($width, $height, $allowEnlarge);
    }

	/**
	 * Crops image according to the given width, height and crop position
	 * @param int $width
	 * @param int $height
	 * @param int $position
	 * @param bool $allowEnlarge
	 * @return static
	 */
	public function crop($width, $height, $position = self::CROP_CENTER, $allowEnlarge = false): void
	{
        if (!$allowEnlarge) {
            if ($width > $this->w) {
                $width  = $this->w;
            }
            if ($height > $this->h) {
                $height = $this->h;
            }
        }

		$sourceRatio = $this->w / $this->h;
        $destRatio = $width / $height;

		$x = 0;
		$y = 0;

        if ($destRatio < $sourceRatio) {
            $this->resizeToHeight($height, $allowEnlarge);
			$x = $this->getCropPosition($width, $this->w, $position);
        } else {
            $this->resizeToWidth($width, $allowEnlarge);
			$y = $this->getCropPosition($height, $this->h, $position);
        }

		// create new image
		$image = imagecreatetruecolor($width, $height);
		imagecopyresampled($image, $this->resource, 0, 0, $x, $y, $width, $height, $width, $height);

		$this->destroyResource($this->resource);
		$this->resource = $image;
		$this->ping();
	}

    /**
     * Gets crop position (X or Y) according to the given position
     * @param int $desired
     * @param int $source
     * @param int $position
     * @return float|int
     */
    protected function getCropPosition($desired, $source, $position = Image::CROP_CENTER)
    {
        $pos = 0;

        switch ($position) {
			case Image::CROP_BOTTOM:
			case Image::CROP_RIGHT:
				$pos = $source - $desired;
				break;
			case Image::CROP_CENTER:
				$pos = ($source / 2) - ($desired / 2);
				break;
        }

        return $pos;
    }

	/**
	 * Crop
	 * @param int $width
	 * @param int $height
	 * @param bool $allowEnlarge
	 */
	public function thumbnail($width, $height, $allowEnlarge = false): void
	{
		//$this->debug("crop($startX, $startY, $width, $height)\t\tcalling -> cropImage($startX, $startY, $width, $height)");
        if (!$allowEnlarge) {
            if ($width > $this->w) {
                $width  = $this->w;
            }
            if ($height > $this->h) {
                $height = $this->h;
            }
        }

		// resize to best fit current working image
		$sourceRatio = $this->h / $this->w;
        $sourceWidth = $width;
        $sourceHeight = round($sourceWidth * $sourceRatio);

		if ($sourceHeight > $height) {
            $sourceHeight = $height;
            $sourceWidth = $sourceHeight / $sourceRatio;
        }

		$this->resizeImage($sourceWidth, $sourceHeight);

		// create new image
		$image = imagecreatetruecolor($width, $height);

		// Filling final thumbnail canvas with white
		if ($width !== $sourceWidth || $height !== $sourceHeight) {
			$white = imagecolorallocate($image, 255, 255, 255);
			imagefill($image, 0, 0, $white);
		}

		$x0 = ($width - $sourceWidth) / 2;
		$y0 = ($height - $sourceHeight) / 2;

		// Copying a $temp_width x $temp_height image from the temporary
		// thumbnail at (0, 0) and placing it in the final
		// thumbnail at ($x0, $y0)
		imagecopyresampled($image, $this->resource, $x0, $y0, 0, 0, $sourceWidth, $sourceHeight, $sourceWidth, $sourceHeight);

		$this->destroyResource($this->resource);
		$this->resource = $image;
		$this->ping();
	}

	/**
	 * Rotates an image.
	 * @param int $angle Rotation angle in degrees. Supports negative values.
	 * @param mixed $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Transparent by default.
	 * @return void
	 */
	public function rotate($angle, $bgColor = self::COLOR_TRANSPARENT): void
	{
		$angle = (int)$angle;
		if (!is_numeric($angle) || $angle===0 || $angle<-359 || $angle > 359) {
			return;
		}

		$this->debug("rotate($angle)\t\tcalling -> rotateImage($angle)");
		$this->rotateImage($angle, $bgColor);
		$this->ping();
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
	 * Converts a hex color value to its RGB equivalent
	 * @param string $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @return array|bool
	 */
	protected function hexToRgba($color)
	{
		$r = '00';
		$g = '00';
		$b = '00';
		$a = 127;
				
		if ($color!==-1 && $color!==0 && is_string($color)) {
			
			$color = trim($color, '#');
			$a = 0;

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
			}
		}

		return ['r' => hexdec($r), 'g' => hexdec($g), 'b' => hexdec($b), 'a' => $a];
	}

}