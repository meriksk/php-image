<?php

namespace meriksk\PhpImage\driver;

use Exception;
use Imagick;
use	meriksk\PhpImage\Image;
use meriksk\PhpImage\BaseImage;

/**
 * ImageImagick class file. 
 */
class ImageImagick extends BaseImage
{
	
	protected static $driver = Image::DRIVER_IMAGICK;


	/**
	 * Load image resource
	 * @param string $file Image path or the image data, as a string.
	 * @return void
	 */
	protected function _loadFromFile($file)
	{
		// new image resource
		$this->resource = new Imagick();
		$this->resource->readImage(realpath($file));
		$this->resource->setColorspace(Imagick::COLORSPACE_SRGB);
	}
	
	protected function loadImageFromString()
	{
		$this->resource = new Imagick();
		$this->resource->readImageBlob($this->dataString);
		$this->resource->setColorspace(Imagick::COLORSPACE_SRGB);
	}
	
	/**
	 * Fetch basic attributes about the image
	 * @param string $filename Filename or image data string
	 * @throws Exception
	 */
	protected function _ping($filename = null)
	{
		$result = false;

		if ($filename) {
			$result = $this->resource->pingImage($filename);
		} else {
			if ($this->dataString) {
				$result = $this->resource->pingImageBlob($this->dataString);
			} elseif ($this->dataBase64String) {
				$result = $this->resource->pingImageBlob(base64_decode($this->dataBase64String));
			} else {
				$result = $this->resource->pingImage($this->path);
			}
		}

		if ($result) {
			$this->w = $this->resource->getImageWidth();
			$this->h = $this->resource->getImageHeight();
			$this->mime_type = $this->resource->getImageMimeType();
		
			switch ($this->mime_type) {
				case 'image/jpeg':
				case 'image/x-jpeg':
					$this->mime_type = 'image/jpeg';
					$this->type = IMAGETYPE_JPEG;
					$this->extension = 'jpg';
					break;
				case 'image/png':
					$this->type = IMAGETYPE_PNG;
					$this->extension = 'png';
					break;
				case 'image/gif':
					$this->type = IMAGETYPE_GIF;
					$this->extension = 'gif';
					break;
				default:
					throw new Exception('Unknown image type: '. $this->mime_type);
			}
			
			$this->orientation = $this->getOrientation();

		}
		
		return $result;
	}
	

	/**
	 * Destroy an image resource
	 * @param resource $resource
	 * @return $this
	 */
	protected function _destroy($resource)
	{
		if ($this->isResource($resource)) {
			$resource->clear();
		}
		
		$resource = NULL;
		$this->resource = NULL;
		return $this;
	}

	/**
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $filename
	 * @param int $quality
	 * @param string $mimeType
	 * @return bool
	 */
	protected function _save($filename, $quality, $mimeType)
	{
		// Create the image
		switch ($mimeType) {
			case 'image/jpeg':
				$this->resource->setImageFormat('jpg');
				break;
			case 'image/png':
				$this->resource->setImageFormat('png');
				break;
			case 'image/gif':
				$this->resource->setImageFormat('gif');
				break;
			default:
				throw new Exception('Unsupported format');
		}

		$this->resource->setImageCompression(imagick::COMPRESSION_JPEG);
		$this->resource->setImageCompressionQuality($quality);
		$this->resource->stripImage();

		$result = $this->resource->writeImage($filename);

		if (!$result) {
			throw new Exception('Unable to save image: ' . $filename);
		}

		return true;
	}

	/**
	 * Outputs image without saving
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $mimeType
	 * @return array
	 */
	protected function _output($quality, $mimeType)
	{
		switch ($mimeType) {
			case 'image/jpeg':
				$this->resource->setImageFormat('jpeg');
				break;
			case 'image/png':
				$this->resource->setImageFormat('png');
				break;
			case 'image/gif':
				$this->resource->setImageFormat('gif');
				break;
			default:
				throw new Exception('Unsupported image format: ' . $mimeType);
		}

		return [
			'mime_type' => $mimeType,
			'data' => $this->resource->getImageBlob(),
		];
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	protected function _resize($width, $height)
	{
		//$this->resource->adaptiveResizeImage($width, $height, true);
		$this->resource->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
	}

	/**
	 * Extracts a region of the image.
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	protected function _crop($x, $y, $width, $height)
	{
		if ($shrink === true) {
			if ($this->width > $this->height) {
				$x0 = 0;
				$y0 = -1 * round(($height - $this->height) / 2);
			} else {
				$x0 = -1 * round(($width - $this->width) / 2);
				$y0 = 0;
			}

			$this->resource->setGravity(Imagick::GRAVITY_CENTER);
			$this->resource->setImageBackgroundColor('white');
			$this->resource->extentImage($width, $height, $x0, $y0);
		} else {
			$this->resource->cropThumbnailImage($width, $height);
		}
	}
	
	/**
	 * Thumbnail
	 * @param int $width
	 * @param int $height
	 * @param bool $fill
	 * @param bool $allowEnlarge
	 */
	protected function _thumbnail($width, $height, $fill = false, $allowEnlarge = false)
	{
	}

	/**
	 * Flips an image using a given mode
	 * @param int $mode
	 * @return void
	 */
	protected function _flip($mode)
	{
		$flipMode = null;
		switch ($mode) {
			case IMG_FLIP_HORIZONTAL:
			case Image::FLIP_HORIZONTAL:
				$this->resource->flopImage();
				break;
			case IMG_FLIP_VERTICAL:
			case Image::FLIP_VERTICAL:
				$this->resource->flipImage();
				break;
			case IMG_FLIP_BOTH:
			case Image::FLIP_BOTH:
				$this->resource->flopImage();
				$this->resource->flipImage();
				break;
		}
	}
	
	/**
	 * Rotate an image with a given angle and background color
	 * @param float $angle <p>Rotation angle, in degrees. The rotation angle is 
	 * interpreted as the number of degrees to rotate the image anticlockwise.</p>
	 * @param string|array $bgd_color <p>Specifies the color of the uncovered 
	 * zone after the rotation</p> Transparent by default.
	 * @return void
	 */
	public function _rotate($angle, $bgColor = Image::COLOR_TRANSPARENT)
	{
		if ($angle < 0) { $angle = 360 - abs($angle); }
		$this->resource->rotateImage(new ImagickPixel(), $angle);
	}

	/**
	 * Set background color_
	 * @param string|array $color
	 * @return void
	 */
	protected function _setBackgroundColor($color)
	{
		$hex = $this->rgba2Hex($color);
		$this->resource->setImageBackgroundColor($hex);
	}
	
	
	
	/**
	 * Create an image from scratch
	 * @param int $width Image width
	 * @param int|null $height If omitted - assumed equal to $width
	 * @param null|string $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @return CImage
	 */
	public function createImage($width, $height = NULL, $color = NULL)
	{
		$w = ($width > 0) ? (int)$width : 800;
		$h = ($height > 0) ? (int)$height : 600;

		$image = new ImageImagick();
		$image->resource = new Imagick();
		$image->resource->newImage($w, $h, $color);
		$image->resource->setImageFormat('png');

		$image->width = $w;
		$image->height = $h;
		$image->extension = 'png';
		$image->mimeType = 'image/png';
		$image->dateCreated = time();

		// original image
		$image->imageOriginal = clone $image;
		$image->imageOriginal->resource = clone $image->resource;

		return $image;
	}


	/**
	 * Revert an image
	 * @return CImage
	 */
	protected function revertImage()
	{
		// destroy working image
		$this->destroyResource();
		$this->resource = clone $this->imageOriginal->resource;
	}

	/**
	 * Get meta data of image or base64 string
	 * @return array
	 * @throws Exception
	 */
	protected function readImageMetaData()
	{
		if ($this->resource) {
			$this->width = $this->resource->getImageWidth();
			$this->height = $this->resource->getImageHeight();
			$this->mimeType = $this->resource->getImageMimeType();
			$this->orientation = $this->getOrientation();
			$this->dateCreated = $this->getExifData('dateCreated', NULL, $this->path);
			$this->extension = str_replace(['jpeg'], ['jpg'], strtolower($this->resource->getImageFormat()));
		}

		return $this->getInfo();
	}

	/**
	 * Reads the image EXIF data.
	 * This method is available if Imagick has been compiled
	 * against ImageMagick version 6.3.6 or newer.
	 * @param string $path
	 * @return bool|array
	 */
	public function readImageExifData($path)
	{
		$image = new Imagick;
		$image->pingImage($path);
		$data = $image->getImageProperties('exif:*');
		return is_array($data) ? $data : false;
	}
	
	/**
	 * Load watermark image into the memory
	 * @param string $path
	 * @return bool
	 */
	protected function loadImageWatermark($path)
	{
		$image = new Imagick();
		$result = $image->readImage($path);

		if ($result) {
			self::$watermark['image'] = $image;
			self::$watermark['width'] = $image->getImageWidth();
			self::$watermark['height'] = $image->getImageHeight();
			return true;
		}

		return false;
	}

	/**
	 * Add watermark to image resource
	 * @param array $origin
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	protected function addImageWatermark($origin)
	{
		if (!empty($origin) && is_array($origin)) {
			// Overlay the watermark on the original image
			return $this->resource->compositeImage(
				self::$watermark['image'],
				Imagick::COMPOSITE_OVER,
				$origin[0],
				$origin[1]
			);
		} else {
			return false;
		}
	}


	public function fill() {}

}