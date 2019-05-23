<?php

namespace merik\Image;

use Exception;
use Imagick;
use merik\Image\Image;

/**
 * ImageImagick class file. 
 */
class ImageImagick extends Image
{

	/**
	 * Load image resource
	 * @param string $image Image path or the image data, as a string.
	 * @param bool $fromString Load image as a string.
	 * @return Image 
	 * @throws Exception
	 */
	protected function loadImage($image, $fromString = FALSE)
	{
		$this->destroy();
		$this->resource = new Imagick();

		if ($fromString===true) {
			$this->debug("loadImage()\tfrom string");
			$result = $this->resource->readimageblob($image);
			
			if (!$result) {
				throw new Exception('Image type is unsupported, the data is not in a recognised format, or the image is corrupt and cannot be loaded.');
			}
			
		} else {
			$this->debug("loadImage()\t\tfrom path");
			$this->path = trim($image);
			$this->resource->readImage($image);
		}

		$this->resource->setColorspace(Imagick::COLORSPACE_SRGB);

		// read image data
		$this->readMetaData();

		// auto rotate
		$this->autoRotate();

		// original image
		$this->imageOriginal = clone $this;
		$this->imageOriginal->resource = clone $this->resource;

		return $this;
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
	 * Outputs image without saving
	 * @param null|string $format If omitted or null - format of original file will be used, may be gif|jpg|png
	 * @param int|null $quality Output image quality in percents 0-100
	 * @throws Exception
	 */
	public function output($format = NULL, $quality = NULL)
	{
		// Determine quality
		$quality = $quality ? (int)$quality : $this->quality;

		// Determine mimetype
		switch (strtolower($format)) {
			case 'gif':
				$mimetype = 'image/gif';
				$this->resource->setImageFormat('gif');
				break;
			case 'jpeg':
			case 'jpg':
				$mimetype = 'image/jpeg';
				$this->resource->setImageFormat('jpg');
				break;
			case 'png':
				$mimetype = 'image/png';
				$this->resource->setImageFormat('png');
				break;
			default:
				$mimetype = $this->mimeType;
				break;
		}

		// Output the image
		header('Content-Type: ' . $mimetype);
		echo $this->resource->getImageBlob();
	}

	/**
	 * Revert an image
	 * @return CImage
	 */
	protected function revertImage()
	{
		// destroy working image
		$this->destroyResource($this->resource);
		$this->resource = NULL;
		$this->resource = clone $this->imageOriginal->resource;
	}

	/**
	 * Destroy an image resources
	 * @param Resource $image
	 */
	protected function destroyResource($image = NULL)
	{
		if ($image === NULL) {
			$image = $this->resource;
		}

		if ($this->isResource($image)) {
			$image->clear();
		}
		
		$image = NULL;
		$this->resource = NULL;
	}

	/**
	 * Fetch basic attributes about the image.
	 * @param string $path The filename to read the information from.
	 * @return $array
	 * @throws Exception
	 */
	public function pingImage($path)
	{
		$data = false;
		if ($path && file_exists($path)) {
			$image = new ImageImagick();
			$image->path = $path;
			$image->resource = new Imagick();
			$result = $image->resource->pingImage($path);

			if ($result) {
				$image->width = $image->resource->getImageWidth();
				$image->height = $image->resource->getImageHeight();
				$image->mimeType = $image->resource->getImageMimeType();
				$image->orientation = $image->getOrientation($image->width, $image->height);
				$image->dateCreated = $image->getExifData('dateCreated', NULL, $image->path);
				$image->extension = str_replace(['jpeg'], ['jpg'], strtolower($image->resource->getImageFormat()));
			}

			$data = $image->getInfo();
			$image->destroy();
		}
		
		return $data;
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
	 * Creates a crop thumbnail
	 * @param int $width The width of the thumbnail
	 * @param int $height The Height of the thumbnail
	 * @param bool $shrink
	 * @return CImageImagick
	 */
	protected function cropImage($width, $height, $shrink = FALSE)
	{
		if ($shrink === true) {

			if ($this->width > $this->height) {
				$x0 = 0;
				$y0 = -(($height - $this->height) / 2);
			} else {
				$x0 = -(($width - $this->width) / 2);
				$y0 = 0;
			}

			$this->resource->setGravity(Imagick::GRAVITY_CENTER);
			$this->resource->setImageBackgroundColor('white');
			$this->resource->extentImage($width, $height, $x0, $y0);
		} else {
			$this->resource->cropThumbnailImage($width, $height);
		}

		return $this;
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width
	 * @param int $height
	 * @return CImage
	 */
	protected function resizeImage($width, $height)
	{
		//$this->resource->adaptiveResizeImage($width, $height, true);
		$this->resource->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
		return $this;
	}

	/**
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $path If omitted - original file will be overwritten
	 * @param null|int $quality	Output image quality in percents 0-100
	 * @param null|string $format The format to use; determined by file extension if null
	 * @return CImage
	 * @throws Exception
	 */
	protected function saveImage($path, $quality = NULL, $format = NULL)
	{
		// Create the image
		switch (strtolower($format)) {
			case 'jpg':
			case 'jpeg':
				$this->resource->setImageFormat('jpg');
				break;
			case 'png':
				$this->resource->setImageFormat('png');
				break;
			case 'gif':
				$this->resource->setImageFormat('gif');
				break;
			default:
				throw new Exception('Unsupported format');
		}

		$this->resource->setImageCompression(imagick::COMPRESSION_JPEG);
		$this->resource->setImageCompressionQuality($quality);
		$this->resource->stripImage();

		$result = $this->resource->writeImage($path);

		if (!$result) {
			throw new Exception('Unable to save image: ' . $path);
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
	public function rotateImage($angle, $bgColor = self::COLOR_TRANSPARENT)
	{
		if ($angle < 0) { $angle = 360 - abs($angle); }
		$this->resource->rotateImage(new ImagickPixel(), $angle);
		return $this;
	}

	/**
	 * Flips an image using a given mode
	 * @param int $mode
	 * @return CImageImagick
	 */
	protected function flipImage($mode = self::FLIP_VERTICAL)
	{
		if ($mode===self::FLIP_HORIZONTAL) {
			$this->resource->flopImage();
		} elseif ($mode===self::FLIP_VERTICAL) {
			$this->resource->flipImage();
		} elseif ($mode===self::FLIP_BOTH) {
			$this->resource->flopImage();
			$this->resource->flipImage();
		}

		return $this;
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