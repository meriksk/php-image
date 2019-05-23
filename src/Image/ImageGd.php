<?php

namespace merik\Image;

use Exception;
use merik\Image\Image;

/**
 * CImageGd class.
 * GD2 Image Lib
 */
class ImageGd extends Image
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

		if ($fromString===true) {
			$this->debug("loadImage()\tfrom string");
			$info = getimagesizefromstring($this->imageString);
			$this->resource = imagecreatefromstring($this->imageString);
			
			if ($this->resource) {
				throw new Exception('Image type is unsupported, the data is not in a recognised format, or the image is corrupt and cannot be loaded.');
			}

		} else {

			$this->debug("loadImage()\t\tfrom path");
			$this->path = trim($image);
			$info = getimagesize($this->path);

			switch ($info['mime']) {
				case 'image/jpg':
				case 'image/jpeg':
					$this->resource = imagecreatefromjpeg($this->path);
					$this->extension = 'jpg';
					break;
				case 'image/png':
					$this->resource = imagecreatefrompng($this->path);
					$this->extension = 'png';
					break;
				case 'image/gif':
					$this->resource = imagecreatefromgif($this->path);
					$this->extension = 'gif';
					break;
				default:
					throw new Exception('Invalid image: '. $this->path);
			}
			
			$this->mimeType = $info['mime'];
		}

		imagesavealpha($this->resource, true);
		imagealphablending($this->resource, true);

		// read image data
		$this->readMetaData();

		// auto rotate
		$this->autoRotate();

		// original image
		$this->imageOriginal = clone $this;
		$this->imageOriginal->resource = imagecreatetruecolor($this->width, $this->height);
		imagecopy($this->imageOriginal->resource, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
		imagesavealpha($this->imageOriginal->resource, true);
		imagealphablending($this->imageOriginal->resource, true);
		$this->imageOriginal->readMetaData();
		$this->imageOriginal->autoRotate();

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

		$image = new ImageGd();
		$image->resource = imagecreatetruecolor($w, $h);
		imagesavealpha($image->resource, true);
		imagealphablending($image->resource, true);
		
		$image->width = $w;
		$image->height = $h;
		$image->extension = 'png';
		$image->mimeType = 'image/png';
		$image->dateCreated = time();

		// original image
		$image->imageOriginal = clone $image;
		$image->imageOriginal->resource = imagecreatetruecolor($w, $h);
		imagesavealpha($image->imageOriginal->resource, true);
		imagealphablending($image->imageOriginal->resource, true);

		if ($color) {
			$image->fill($color);
			$image->imageOriginal->fill($color);
		}

		return $image;
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

		$dimensions = $this->imageOriginal->getDimensions();

		$this->resource = imagecreatetruecolor($dimensions[0], $dimensions[1]);
		imagecopy($this->resource, $this->imageOriginal->resource, 0, 0, 0, 0, $dimensions[0], $dimensions[1]);

		return $this;
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
			imagedestroy($image);
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

			$image = new ImageGd();
			$image->path = $path;

			$info = getimagesize($path);
			if ($info) {
				$image->width = $info[0];
				$image->height = $info[1];
				$image->mimeType = $info['mime'];
				$image->orientation = $image->getOrientation($info[0], $info[1]);
				$image->dateCreated = $image->getExifData('dateCreated', NULL, $image->path);
				$image->extension = str_replace(['jpeg'], ['jpg'], image_type_to_extension($info[2], false));
			}

			$data = $image->getInfo();
			$image->destroy();
		}

		return $data;
	}

	/**
	 * Updates meta data of image
	 * @return array
	 * @throws Exception
	 */
	protected function readImageMetaData()
	{
		$this->width = imagesx($this->resource);
		$this->height = imagesy($this->resource);
		//$this->mimeType = $info['mime'];
		$this->orientation = $this->getOrientation($this->width, $this->height);
		$this->dateCreated = $this->getExifData('dateCreated', NULL, $this->path);

		//$extension = str_replace(['jpeg'], ['jpg'], image_type_to_extension($info[2], false));
		//$this->extension = $extension;

		return $this->getInfo();
	}

	/**
	 * Reads the image EXIF data.
	 * @param string $path
	 * @return bool|array
	 */
	public function readImageExifData($path)
	{
		if ($path && extension_loaded('exif')) {
			$data = @exif_read_data($path);
			return is_array($data) ? $data : false;
		}

		return false;
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
			case 'gif':
				$result = imagegif($this->resource, $path);
				break;
			case 'jpg':
			case 'jpeg':
				imageinterlace($this->resource, true);
				$result = imagejpeg($this->resource, $path, $quality);
				break;
			case 'png':
				$result = imagepng($this->resource, $path, round(9 * $quality / 100));
				break;
			default:
				throw new Exception('Unsupported format');
		}

		if (!$result) {
			throw new Exception('Unable to save image: ' . $path);
		}

		return $this;
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
				break;
			case 'jpg':
			case 'jpeg':
				imageinterlace($this->resource, true);
				$mimetype = 'image/jpeg';
				break;
			case 'png':
				$mimetype = 'image/png';
				break;
			default:
				$mimetype = $this->mimeType;
				break;
		}

		// Output the image
		header('Content-Type: '.$mimetype);
		switch ($mimetype) {
			case 'image/gif':
				imagegif($this->resource);
				break;
			case 'image/jpeg':
				imageinterlace($this->resource, true);
				imagejpeg($this->resource, null, round($quality));
				break;
			case 'image/png':
				imagepng($this->resource, null, round(9 * $quality / 100));
				break;
			default:
				throw new Exception('Unsupported image format: '.$this->path);
		}
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width
	 * @param int $height
	 * @return CImage
	 */
	protected function resizeImage($width, $height)
	{
		// Generate new GD image
		$new = imagecreatetruecolor($width, $height);

		// Preserve transparency in GIFs
		if ($this->extension === 'gif') {
			$transparentIndex = imagecolortransparent($this->resource);
			$palletsize = imagecolorstotal($this->resource);
			if ($transparentIndex >= 0 && $transparentIndex < $palletsize) {
				$transparentColor = imagecolorsforindex($this->resource, $transparentIndex);
				$transparentIndex = imagecolorallocate($new, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
				imagefill($new, 0, 0, $transparentIndex);
				imagecolortransparent($new, $transparentIndex);
			}
		// Preserve transparency in PNGs (benign for JPEGs)
		} else {
			imagealphablending($new, false);
			imagesavealpha($new, true);
		}

		// resize
		imagecopyresampled($new, $this->resource, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

		// update meta data
		$this->resource = $new;

		return $this;
	}

	/**
	 * Creates a crop thumbnail
	 * @param int $width The width of the thumbnail
	 * @param int $height The Height of the thumbnail
	 * @param bool $shrink
	 * @return Image
	 */
	protected function cropImage($width, $height, $shrink = FALSE)
	{
		$x0 = ($width - $this->width) / 2;
		$y0 = ($height - $this->height) / 2;

		// Positioning the temporary $temp_width x $temp_height thumbnail in
		// the center of the final $desiredWidth x $desiredHeight thumbnail...
		// Creating final thumbnail canvas at $desiredWidth x $desiredHeight
		$new = imagecreatetruecolor($width, $height);

		imagealphablending($new, true);
		imagesavealpha($new, true);

		if ($shrink === true) {
			// Filling final thumbnail canvas with white
			imagefill($new, 0, 0, imagecolorallocate($this->resource, 255, 255, 255));
		}

		// Copying a $temp_width x $temp_height image from the temporary
		// thumbnail at (0, 0) and placing it in the final
		// thumbnail at ($x0, $y0)
		imagecopyresampled(
			$new,
			$this->resource,
			$x0,
			$y0,
			0,
			0,
			$this->width,
			$this->height,
			$this->width,
			$this->height
		);

		$this->resource = $new;

		return $this;
	}

	/**
	 * Fill image with color
	 * @param string $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @return CImage
	 */
	public function fill($color = '000000')
	{
		$rgba = $this->normalizeColor($color);
		$fillColor = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
		imagealphablending($this->resource, false);
		imagesavealpha($this->resource, true);

		// imagefill() uses flood fill, which is quite slow compared to 
		// just painting a color in a rectangle without regard for the 
		// content of the image. So imagefilledrectangle() will be a lot quicker.
		imagefilledrectangle($this->resource, 0, 0, $this->width, $this->height, $fillColor);

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
		if ($angle < 0) {
			$angle = abs($angle);
		} else {
			$angle = 360 - $angle;
		}

		// Perform the rotation
		$rgba = $this->normalizeColor($bgColor);
		$color = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
		$new = imagerotate($this->resource, $angle, $color);
		imagesavealpha($new, true);
		imagealphablending($new, true);

		$this->resource = $new;
		return $this;
	}

	/**
	 * Flips an image using a given mode
	 * @param int $mode
	 * @return CImageGd
	 */
	protected function flipImage($mode = self::FLIP_VERTICAL)
	{
		$this->resource = imageflip($this->resource, $mode);
		return $this;
	}

	/**
	 * Add watermark to image resource
	 * @param array $origin
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addImageWatermark($origin)
	{
		if (!empty($origin) && is_array($origin)) {

			// Copy the stamp image onto our photo using the margin offsets and
			// the photo width to calculate positioning of the stamp.
			return imagecopy(
				$this->resource,
				self::$watermark['image'],
				$origin[0], $origin[1],
				0, 0,
				self::$watermark['width'], self::$watermark['height']
			);
		} else {
			return false;
		}
	}

	/**
	 * Load watermark image into the memory
	 * @param string $path
	 * @return bool
	 */
	protected function loadImageWatermark($path)
	{
		// load image into memory
		$image = imagecreatefrompng($path);

		if ($image) {
			self::$watermark['image'] = $image;
			self::$watermark['width'] = imagesx($image['image']);
			self::$watermark['height'] = imagesy($image['image']);
			return true;
		}

		return false;
	}

	/**
	 * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
	 * @param string $overlay An image path or a CImage object
	 * @param string $position center|top|left|bottom|right|top left|top right|bottom left|bottom right
	 * @param array $options
	 *	  opacity int Overlay opacity 0-1
	 *	  offsetX int Horizontal offset in pixels
	 *	  offsetY int Vertical offset in pixels
	 *	  maxWidth int Maximum overlay width
	 *	  maxHeight int Maximum overlay height
	 *
	 * @return CImage
	 */
	public function overlay($overlay, $position = 'center', array $options = [])
	{
		// Load overlay image
		if (!($overlay instanceof CImage)) {
			$overlay = CImage::load($overlay);
		}

		// settings
		$settings = array_merge([
			'opacity' => 1,
			'offsetX' => NULL,
			'offsetY' => NULL,
			'maxWidth' => NULL,
			'maxHeight' => NULL,
		], $options);

		// check settings
		$settings['opacity'] = is_numeric($settings['opacity']) ? intval($settings['opacity']) * 100 : 1;
		$settings['offsetX'] = is_numeric($settings['offsetX']) ? (int)$settings['offsetX'] : 0;
		$settings['offsetY'] = is_numeric($settings['offsetY']) ? (int)$settings['offsetY'] : 0;
		$settings['maxWidth'] = ($settings['maxWidth'] > 0) ? (int)$settings['maxWidth'] : $this->width;
		$settings['maxHeight'] = ($settings['maxHeight'] > 0) ? (int)$settings['maxHeight'] : $this->height;


		// resize overlay if needed
		if (
			($settings['maxWidth'] > 0 && ($overlay->width > $settings['maxWidth']))
			||
			($settings['maxHeight'] > 0 && ($overlay->height > $settings['maxHeight']))
		) {

			// determine aspect ratios
			$aspectRatioBefore = $overlay->height / $overlay->width;
			$aspectRatioAfter = $settings['maxHeight'] / $settings['maxWidth'];

			// fit to height/width
			if ($aspectRatioAfter < $aspectRatioBefore) {
				$overlay->fitToHeight($settings['maxHeight']);
			} else {
				$overlay->fitToWidth($settings['maxWidth']);
			}
		}

		// Determine position
		switch (strtolower($position)) {
			case 'top left':
				$x = 0 + $settings['offsetX'];
				$y = 0 + $settings['offsetY'];
				break;
			case 'top right':
				$x = $this->width - $overlay->width + $settings['offsetX'];
				$y = 0 + $settings['offsetY'];
				break;
			case 'top':
			case 'top center':
				$x = ($this->width / 2) - ($overlay->width / 2) + $settings['offsetX'];
				$y = 0 + $settings['offsetY'];
				break;
			case 'bottom left':
				$x = 0 + $settings['offsetX'];
				$y = $this->height - $overlay->height + $settings['offsetY'];
				break;
			case 'bottom right':
				$x = $this->width - $overlay->width + $settings['offsetX'];
				$y = $this->height - $overlay->height + $settings['offsetY'];
				break;
			case 'bottom':
			case 'bottom center':
				$x = ($this->width / 2) - ($overlay->width / 2) + $settings['offsetX'];
				$y = $this->height - $overlay->height + $settings['offsetY'];
				break;
			case 'left':
				$x = 0 + $settings['offsetX'];
				$y = ($this->height / 2) - ($overlay->height / 2) + $settings['offsetY'];
				break;
			case 'right':
				$x = $this->width - $overlay->width + $settings['offsetX'];
				$y = ($this->height / 2) - ($overlay->height / 2) + $settings['offsetY'];
				break;
			case 'center':
			case 'center center':
			default:
				$x = ($this->width / 2) - ($overlay->width / 2) + $settings['offsetX'];
				$y = ($this->height / 2) - ($overlay->height / 2) + $settings['offsetY'];
				break;
		}

		// Perform the overlay
		$this->imagecopymergeAlpha($this->resource, $overlay->resource, $x, $y, 0, 0, $overlay->width, $overlay->height, $settings['opacity']);
		return $this;
	}

	/**
	 * Add text to an image
	 *
	 * @param string $text
	 * @param string $fontFile
	 * @param float|int $fontSize
	 * @param mixed $color
	 * @param string $position
	 * @param array $options
	 *
	 *		int $offsetX
	 *		int $offsetY
	 *		string|array $strokeColor
	 *		string $strokeSize
	 *		string $alignment
	 *		int $letterSpacing
	 *
	 * @return CImage
	 * @throws Exception
	 */
	public function text($text, $fontFile, $fontSize = 12, $color = true, $position = 'center', array $options = [])
	{

		// check font
		if ((substr($fontFile, '1')!=='/') && !file_exists($fontFile) || !is_readable($fontFile)) {
			throw new Exception('Unable to load font: '.$fontFile);
		}

		// additional options
		$opt = array_merge([
			'x' => NULL,
			'y' => NULL,
			'offsetX' => 0,
			'offsetY' => 0,
			'strokeColor' => NULL,
			'strokeSize' => NULL,
			'alignment' => NULL,
			'letterSpacing' => 0,
			// @todo - this method could be improved to support the text angle
			'angle' => 0,
		], $options);

		// determine textbox size
		$box = imagettfbbox($fontSize, $opt['angle'], $fontFile, $text);

		$boxWidth = abs($box[2] - $box[0]);
		$boxHeight = abs($box[7] - $box[1]);

		// downsize font by 20%
		if ($boxWidth >= $this->width) {
			$fontSize = $fontSize - ((20 * $fontSize)/100);
		}

		// fixed position
		if (is_numeric($opt['x']) && is_numeric($opt['y'])) {
			$x = intval($opt['x']);
			$y = intval($opt['y']);
		} else {

			// Determine position
			switch (strtolower($position)) {
				case 'top left':
					$x = 0 + $opt['offsetX'];
					//$y = 0 + $opt['offsetY'] + $boxHeight;
					$y = 0 + $opt['offsetY'];
					break;
				case 'top right':
					$x = $this->width - $boxWidth + $opt['offsetX'];
					//$y = 0 + $opt['offsetY'] + $boxHeight;
					$y = 0 + $opt['offsetY'];
					break;
				case 'top':
				case 'top center':
					$x = ($this->width / 2) - ($boxWidth / 2) + $opt['offsetX'];
					//$y = 0 + $opt['offsetY'] + $boxHeight;
					$y = 0 + $opt['offsetY'];
					break;
				case 'bottom left':
					$x = 0 + $opt['offsetX'];
					//$y = $this->height - $boxHeight + $opt['offsetY'] + $boxHeight;
					$y = $this->height - $boxHeight + $opt['offsetY'] + $boxHeight;
					break;
				case 'bottom right':
					$x = $this->width - $boxWidth + $opt['offsetX'];
					$y = $this->height - $boxHeight + $opt['offsetY'] + $boxHeight;
					break;
				case 'bottom':
				case 'bottom center':
					$x = ($this->width / 2) - ($boxWidth / 2) + $opt['offsetX'];
					$y = $this->height - $boxHeight + $opt['offsetY'] + $boxHeight;
					break;
				case 'left':
					$x = 0 + $opt['offsetX'];
					$y = ($this->height / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
					break;
				case 'right';
					$x = $this->width - $boxWidth + $opt['offsetX'];
					$y = ($this->height / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
					break;
				case 'center':
				case 'center center':
				default:
					$x = ($this->width / 2) - ($boxWidth / 2) + $opt['offsetX'];
					$y = ($this->height / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
					break;
			}

			// Left aligned text
			if ($opt['alignment'] === 'left') {
				$x = -($x * 2);
			// Right aligned text
			} else if ($opt['alignment'] === 'right') {
				$dimensions = imagettfbbox($fontSize, $opt['angle'], $fontFile, $text);
				$alignmentOffset = abs($dimensions[4] - $dimensions[0]);
				$x = -(($x * 2) + $alignmentOffset);
			}
		}

		// colors array
		$colorArr = [];

		// Determine text color
		if ($color === true || $color === 'auto') {

			// get the index of the color of a pixel
			$index = imagecolorat($this->resource, $x, $y);

			// get the colors for an index
			$rgba = imagecolorsforindex($this->resource, $index);
			$color =
				((strlen(dechex($rgba['red']))===1) ? dechex($rgba['red']) : '') . dechex($rgba['red']) .
				((strlen(dechex($rgba['green']))===1) ? dechex($rgba['green']) : '') . dechex($rgba['green']) .
				((strlen(dechex($rgba['blue']))===1) ? dechex($rgba['blue']) : '') . dechex($rgba['blue']);

			$rgba = $this->normalizeColor($this->oppositeColor($color));
			$colorArr[] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

		} elseif (is_array($color)) {

			foreach($color as $var) {
				$rgba = $this->normalizeColor($var);
				$colorArr[] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
			}

		} else {

			$rgba = $this->normalizeColor($color);
			$colorArr[] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

		}

		// Add the text
		imagesavealpha($this->resource, true);
		imagealphablending($this->resource, true);

		if (!is_null($opt['strokeColor']) && !is_null($opt['strokeSize'])) {

			// Text with stroke
			if (is_array($color) || is_array($opt['strokeColor'])) {
				// Multi colored text and/or multi colored stroke
				if (is_array($opt['strokeColor'])) {
					foreach ($opt['strokeColor'] as $key => $var) {
						$rgba = $this->normalizeColor($opt['strokeColor'][$key]);
						$opt['strokeColor'][$key] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
					}
				} else {
					$rgba = $this->normalizeColor($opt['strokeColor']);
					$opt['strokeColor'] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
				}

				$lettersArray = str_split($text, 1);

				foreach ($lettersArray as $key => $var) {
					if ($key > 0) {
						$dimensions = imagettfbbox($fontSize, $opt['angle'], $fontFile, $lettersArray[$key - 1]);
						$x += abs($dimensions[4] - $dimensions[0]) + $opt['letterSpacing'];
					}

					// If the next letter is empty, we just move forward to the next letter
					if ($var !== ' ') {

						$this->imagettfstroketext($this->resource, $fontSize, $opt['angle'], $x, $y, current($colorArr), current($opt['strokeColor']), $opt['strokeSize'], $fontFile, $var);

						// #000 is 0, black will reset the array so we write it this way
						if (next($colorArr) === false) {
							reset($colorArr);
						}
						// #000 is 0, black will reset the array so we write it this way
						if (next($opt['strokeColor']) === false) {
							reset($opt['strokeColor']);
						}
					}
				}
			} else {
				$rgba = $this->normalizeColor($opt['strokeColor']);
				$opt['strokeColor'] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
				$this->imagettfstroketext($this->resource, $fontSize, $opt['angle'], $x, $y, $colorArr[0], $opt['strokeColor'], $opt['strokeSize'], $fontFile, $text);
			}
		} else {
			// Text without stroke
			if(is_array($color)) {
				// Multi colored text
				$lettersArray = str_split($text, 1);
				foreach ($lettersArray as $key => $var) {
					if ($key > 0) {
						$dimensions = imagettfbbox($fontSize, $opt['angle'], $fontFile, $lettersArray[$key - 1]);
						$x += abs($dimensions[4] - $dimensions[0]) + $opt['letterSpacing'];
					}
					// If the next letter is empty, we just move forward to the next letter
					if ($var !== ' ') {
						imagettftext($this->resource, $fontSize, $opt['angle'], $x, $y, current($colorArr), $fontFile, $var);
						// #000 is 0, black will reset the array so we write it this way
						if (next($colorArr) === false) {
							reset($colorArr);
						}
					}
				}
			} else {
				imagettftext($this->resource, $fontSize, $opt['angle'], $x, $y, $colorArr[0], $fontFile, $text);
			}
		}
		return $this;
	}

	/**
	 * Blur
	 * @param string $type selective|gaussian
	 * @param int $passes Number of times to apply the filter
	 * @return CImage
	 */
	public function blur($type = 'selective', $passes = 1)
	{
		switch (strtolower($type)) {
			case 'gaussian':
			case IMG_FILTER_GAUSSIAN_BLUR:
				$type = IMG_FILTER_GAUSSIAN_BLUR;
				break;
			case 'selective':
			case IMG_FILTER_SELECTIVE_BLUR:
			default:
				$type = IMG_FILTER_SELECTIVE_BLUR;
				break;
		}

		for ($i = 0; $i < $passes; $i++) {
			imagefilter($this->resource, $type);
		}

		return $this;
	}

	/**
	 * Brightness
	 * @param int $level Darkest = -255, lightest = 255
	 * @return CImage
	 */
	public function brightness($level)
	{
		imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, $this->keepWithin($level, -255, 255));
		return $this;
	}


	/**
	 * Contrast
	 * @param int $level  Min = -100, max = 100
	 * @return CImage
	 */
	public function contrast($level)
	{
		imagefilter($this->resource, IMG_FILTER_CONTRAST, $this->keepWithin($level, -100, 100));
		return $this;
	}

	/**
	 * Colorize
	 * @param string $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Where red, green, blue - integers 0-255, alpha - integer 0-127
	 * @param float|int $opacity 0-1
	 * @return CImage
	 */
	public function colorize($color, $opacity)
	{
		$rgba = $this->normalizeColor($color);
		$alpha = $this->keepWithin(127 - (127 * $opacity), 0, 127);
		imagefilter($this->resource, IMG_FILTER_COLORIZE, $this->keepWithin($rgba['r'], 0, 255), $this->keepWithin($rgba['g'], 0, 255), $this->keepWithin($rgba['b'], 0, 255), $alpha);
		return $this;
	}

	/**
	 * Desaturate
	 * @param int $percentage Level of desaturization.
	 * @return CImage
	 */
	public function desaturate($percentage = 100)
	{
		// Determine percentage
		$percentage = $this->keepWithin($percentage, 0, 100);
		if ($percentage === 100) {
			imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		} else {
			// Make a desaturated copy of the image
			$new = imagecreatetruecolor($this->width, $this->height);
			imagealphablending($new, false);
			imagesavealpha($new, true);
			imagecopy($new, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
			imagefilter($new, IMG_FILTER_GRAYSCALE);
			// Merge with specified percentage
			$this->imagecopymergeAlpha($this->resource, $new, 0, 0, 0, 0, $this->width, $this->height, $percentage);
			imagedestroy($new);
		}

		return $this;
	}

	/**
	 * Edge Detect
	 * @return CImage
	 */
	public function edges()
	{
		imagefilter($this->resource, IMG_FILTER_EDGEDETECT);
		return $this;
	}


	/**
	 * Emboss
	 * @return CImage
	 */
	public function emboss()
	{
		imagefilter($this->resource, IMG_FILTER_EMBOSS);
		return $this;
	}

	/**
	 * Invert
	 * @return CImage
	 */
	public function invert()
	{
		imagefilter($this->resource, IMG_FILTER_NEGATE);
		return $this;
	}


	/**
	 * Mean Remove
	 * @return CImage
	 */
	public function meanRemove()
	{
		imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
		return $this;
	}


	/**
	 * Pixelate
	 * @param int $blockSize Size in pixels of each resulting block
	 * @return CImage
	 */
	public function pixelate($blockSize = 10)
	{
		imagefilter($this->resource, IMG_FILTER_PIXELATE, $blockSize, true);
		return $this;
	}

	/**
	 * Sepia effect
	 * @return CImage
	 */
	public function sepia()
	{
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		imagefilter($this->resource, IMG_FILTER_COLORIZE, 100, 50, 0);
		return $this;
	}

	/**
	 * Sketch
	 * @return CImage
	 */
	public function sketch()
	{
		imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
		return $this;
	}

	/**
	 * Smooth
	 * @param int $level  Min = -10, max = 10
	 * @return CImage
	 */
	public function smooth($level)
	{
		imagefilter($this->resource, IMG_FILTER_SMOOTH, $this->keepWithin($level, -10, 10));
		return $this;
	}

	/**
	 * Copy and merge part of an image
	 * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
	 *
	 * @param $dst_im
	 * @param $src_im
	 * @param $dst_x
	 * @param $dst_y
	 * @param $src_x
	 * @param $src_y
	 * @param $src_w
	 * @param $src_h
	 * @param $pct
	 * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 * @link http://www.php.net/manual/en/function.imagecopymerge.php#88456
	 */
	protected function imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
	{
		// Get image width and height and percentage
		$pct /= 100;
		$w = imagesx($src_im);
		$h = imagesy($src_im);

		// Turn alpha blending off
		imagealphablending($src_im, false);

		// Find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
				if ($alpha < $minalpha) {
					$minalpha = $alpha;
				}
			}
		}

		// Loop through image pixels and modify alpha for each
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				// Get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat($src_im, $x, $y);
				$alpha = ($colorxy >> 24) & 0xFF;
				// Calculate new alpha
				if ($minalpha !== 127) {
					$alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
				} else {
					$alpha += 127 * $pct;
				}
				// Get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
				// Set pixel with the new color + opacity
				if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
					return;
				}
			}
		}

		// Copy it
		imagesavealpha($dst_im, true);
		imagealphablending($dst_im, true);
		imagesavealpha($src_im, true);
		imagealphablending($src_im, true);
		imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}

	/**
	 *  Same as imagettftext(), but allows for a stroke color and size
	 *
	 * @param  object &$image A GD image object
	 * @param  float $size The font size
	 * @param  float $angle The angle in degrees
	 * @param  int $x X-coordinate of the starting position
	 * @param  int $y Y-coordinate of the starting position
	 * @param  int &$textcolor The color index of the text
	 * @param  int &$stroke_color The color index of the stroke
	 * @param  int $stroke_size The stroke size in pixels
	 * @param  string $fontfile The path to the font to use
	 * @param  string $text The text to output
	 *
	 * @return array This method has the same return values as imagettftext()
	 */
	protected function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $stroke_size, $fontfile, $text)
	{
		for ($c1 = ($x - abs($stroke_size)); $c1 <= ($x + abs($stroke_size)); $c1++) {
			for ($c2 = ($y - abs($stroke_size)); $c2 <= ($y + abs($stroke_size)); $c2++) {
				$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
			}
		}

		return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
	}

}
