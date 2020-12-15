<?php
/*
if (!function_exists('getimagesizefromstring')) {
			throw new Exception('Required function "getimagesizefromstring()" does not exists.');
		}
 */
namespace meriksk\PhpImage\driver;

use Exception;
use	meriksk\PhpImage\Image;
use meriksk\PhpImage\BaseImage;

/**
 * ImageGd class file
 */
class ImageGd extends BaseImage
{

	protected static $driver = Image::DRIVER_GD;


	////////////////////////////////////////////////////////////////////////////
	// Loaders
	////////////////////////////////////////////////////////////////////////////

	protected function _loadFromFile($file)
	{
		switch ($this->mime_type) {
			case 'image/jpeg':
				$this->resource = @imagecreatefromjpeg($file);
			break;
			case 'image/png':
				$this->resource = @imagecreatefrompng($file);
				if ($this->resource) {
					imagealphablending($this->resource, false);
					imagesavealpha($this->resource, true);
				}
			break;
			case 'image/gif':
				$gif = @imagecreatefromgif($file);
				if ($gif) {
					$width = imagesx($gif);
					$height = imagesy($gif);
					$this->resource = imagecreatetruecolor($width, $height);
					$transparentColor = imagecolorallocatealpha($this->resource, 0, 0, 0, 127);
					imagecolortransparent($this->resource, $transparentColor);
					imagefill($this->resource, 0, 0, $transparentColor);
					imagecopy($this->resource, $gif, 0, 0, 0, 0, $width, $height);
					imagedestroy($gif);
				}
				break;
			default:
				throw new Exception('Unknown image type: '. $this->mime_type);
		}

		if (!$this->resource) {
			throw new Exception('Failed to read image data.');
		}

		// convert pallete images to true color images
		imagepalettetotruecolor($this->resource);

		// Load exif data from JPEG images
		//if($this->mimeType === 'image/jpeg' && function_exists('exif_read_data')) {
		//	$this->exif = @exif_read_data($file);
		//}
	}

	protected function _ping($filename = null)
	{
		// filename
		if ($filename) {
			$info = getimagesize($filename);
			if (!$info) {
				throw new Exception('Failed to read image data.');
			}

			$this->w = $info[0];
			$this->h = $info[1];
			$this->type = $info[2];
			$this->mime_type = $info['mime'];
			$this->orientation = $this->getOrientation();

			// check mime type
			if (strstr($this->mime_type, 'image/') === false) {
				throw new Exception('Unsupported file type.');
			}

		// resource
		} elseif ($this->isResource()) {

			$this->w = imagesx($this->resource);
			$this->h = imagesy($this->resource);

			//$this->type = $info[2];
			//$this->mime_type = $info['mime'];
			//$this->original_orientation = $this->getOrientation($this->original_w, $this->original_h);
			//$this->dateCreated = $image->getExifData('dateCreated', NULL, $image->path);
			//$this->extension = str_replace(['jpeg'], ['jpg'], image_type_to_extension($info[2], false));
		}

		switch ($this->mime_type) {
			case 'image/jpeg':
			case 'image/x-jpeg':
				$this->extension = 'jpg';
				break;
			case 'image/png':
				$this->extension = 'png';
				break;
			case 'image/gif':
				$this->extension = 'gif';
				break;
			default:
				throw new Exception('Unsupported image type. ('. $this->mime_type .')');
		}
	}

	/**
	 * Destroy an image resource
	 * @param resource $resource
	 * @return $this
	 */
	protected function _destroy($resource)
	{
		if ($resource && is_resource($resource) && 'gd'===get_resource_type($resource)) {
			imagedestroy($resource);
		}

		$resource = NULL;
	}

	/**
	 * Save an image. The resulting format will be determined by the file extension.
	 * @param string $filename
	 * @param int $quality
	 * @param string $imageType
	 * @return bool
	 */
	protected function _save($filename, $quality, $mimeType)
	{
		switch ($mimeType) {
			case 'image/gif':
				return @imagegif($this->resource, $filename);
			case 'image/jpeg':
				return @imagejpeg($this->resource, $filename, $quality);
			case 'image/png':
				return @imagepng($this->resource, $filename, round(9 * $quality / 100));
			default:
				return false;
		}
	}

	/**
	 * Outputs image without saving
	 * @param int $quality Output image quality in percents 0-100
	 * @param string $mimeType
	 * @return array
	 * @throws Exception
	 */
	protected function _output($quality, $mimeType)
	{
		$result = false;
		ob_start();

		switch ($mimeType) {
			case 'image/gif':
				$result = imagegif($this->resource);
				break;
			case 'image/jpeg':
				$result = imagejpeg($this->resource, null, $quality);
				break;
			case 'image/png':
				$result = imagepng($this->resource, null, round(9 * $quality / 100));
				break;
			default:
				throw new Exception('Unsupported image format: ' . $mimeType);
		}

		$data = ob_get_clean();

		return $result ? array('mime_type' => $mimeType, 'data' => $data) : false;
	}

	/**
	 * Resize an image to the specified dimensions
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	protected function _resize($width, $height)
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
		imagecopyresampled($new, $this->resource, 0, 0, 0, 0, $width, $height, $this->w, $this->h);
		imagedestroy($this->resource);

		// update meta data
		$this->resource = $new;
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
		// new image
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

			if ($this->bg_color && is_array($this->bg_color)) {
				$color = imagecolorallocate($new, $this->bg_color['r'], $this->bg_color['g'], $this->bg_color['b']);
				imagefill($new, 0, 0, $color);
			}
		}

		imagecopy($new, $this->resource, 0, 0, $x, $y, $this->w, $this->h);
		//imagedestroy($this->resource);

		//$this->resource = imagecrop($this->resource, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
		$this->resource = $new;
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
		// new image
		$thumb = imagecreatetruecolor($width, $height);
		$bgColor = null;

		if (!empty($this->bg_color)) {
			$color = $this->bg_color;

			// alpha
			if ($color['a'] < 1.0) {

				imagealphablending($thumb, true);
				imagesavealpha($thumb, true);

				// alpha channel: php requires value between 0 and 127
				$alpha = (1 - $color['a']) * 127;

				$bgColor = imagecolorallocatealpha($thumb, $color['r'], $color['g'], $color['b'], $alpha);
				imagecolortransparent($thumb, $bgColor);
			// no alpha
			} else {
				$bgColor = imagecolorallocate($thumb, $color['r'], $color['g'], $color['b']);
			}
		} else {
			if ($this->mime_type === 'image/png') {
				imagealphablending($thumb, false);
				imagesavealpha($thumb, true);
				$bgColor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
			}
		}

		//imagecolortransparent($thumb);
		if ($bgColor) {
			imagefill($thumb, 0, 0, $bgColor);
		}

		// determine resize values
		$srcRatio = $this->w / $this->h;
		$dstRatio = $width / $height;

		if ($fill === true) {
			if ($srcRatio >= $dstRatio) {
				$this->resizeToHeight($height, $allowEnlarge);
			} else {
				$this->resizeToWidth($width, $allowEnlarge);
			}
		} else {
			if ($srcRatio >= $dstRatio) {
				$this->resizeToWidth($width, $allowEnlarge);
			} else {
				$this->resizeToHeight($height, $allowEnlarge);
			}
		}

		$x = ($width - $this->w) / 2;
		$y = ($height - $this->h) / 2;

		//imagecopyresampled($thumb, $this->resource, $x, $y, 0, 0, $this->w, $this->h, $this->w, $this->h);
		imagecopy($thumb, $this->resource, $x, $y, 0, 0, $this->w, $this->h);
		imagedestroy($this->resource);
		$this->resource = $thumb;
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
				$flipMode = IMG_FLIP_HORIZONTAL;
				break;
			case IMG_FLIP_VERTICAL:
			case Image::FLIP_VERTICAL:
				$flipMode = IMG_FLIP_VERTICAL;
				break;
			case IMG_FLIP_BOTH:
			case Image::FLIP_BOTH:
				$flipMode = IMG_FLIP_BOTH;
				break;
		}

		if ($flipMode) {
			imageflip($this->resource, $flipMode);
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
	public function _rotate($angle, $bgColor)
	{
		// fix angle value
		$angle = $angle < 0 ? abs($angle) : 360 - $angle;
		$rgba = Image::normalizeColor($bgColor);
		$alpha = (1 - $rgba['a']) * 127;

		// perform the rotation
		if (in_array($this->mime_type, array('image/png', 'image/gif'))) {
			
			$transparent = imagecolorallocatealpha($this->resource, 255, 255, 255, 127);
			$rotated = imagerotate($this->resource, $angle, $transparent, 1);
			imagecolortransparent($rotated);

			$w = imagesx($rotated);
			$h = imagesy($rotated);

			$new = imagecreatetruecolor($w, $h);
			imagealphablending($new, false);
			imagesavealpha($new, true);			

			$bg = imagecolorallocatealpha($new, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
			imagefill($new, 0, 0, $bg);

			imagecopy($new, $rotated, 0, 0, 0, 0, $w, $h);
			imagedestroy($rotated); $rotated = null;

			$this->resource = $new;
		} else {
			$bg = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $alpha);
			$this->resource = imagerotate($this->resource, $angle, $bg);
		}
	}

	/**
	 * Set background color_
	 * @param string|array $color
	 * @return $this
	 */
	protected function _setBackgroundColor($color)
	{
		$bg = imagecreatetruecolor($this->w, $this->h);

		// alpha
		if ($color['a'] < 1.0) {

			if ($this->mime_type === 'image/png') {
				imagealphablending($bg, false);
				imagesavealpha($bg, true);
			}

			// alpha channel: php requires value between 0 and 127
			$alpha = (1 - $color['a']) * 127;
			$bgColor = imagecolorallocatealpha($bg, $color['r'], $color['g'], $color['b'], $alpha);
		// no alpha
		} else {
			$bgColor = imagecolorallocate($bg, $color['r'], $color['g'], $color['b']);
		}

		imagefill($bg, 0, 0, $bgColor);
		imagecopy($bg, $this->resource, 0, 0, 0, 0, $this->w, $this->h);
		$this->resource = $bg;

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
	public function _readExifData($file, $requiredSections = null, $thumbnail = false)
	{
		if ($file && extension_loaded('exif')) {
			if ($requiredSections===null) {
				$requiredSections = 'FILE,COMPUTED,IFD0,COMMENT,EXIF,GPS';
			}

			$data = @exif_read_data($file, $requiredSections, false, $thumbnail);
			return is_array($data) ? $data : false;
		}

		return false;
	}
















	/// 999999999 OOOOLD














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
	public function overlay($overlay, $position = 'center', $options = array())
	{
		// Load overlay image
		if (!($overlay instanceof CImage)) {
			$overlay = CImage::load($overlay);
		}

		if (!is_array($options)) {
			$options = array();
		}
		
		// settings
		$settings = array_merge(array(
			'opacity' => 1,
			'offsetX' => NULL,
			'offsetY' => NULL,
			'maxWidth' => NULL,
			'maxHeight' => NULL,
		), $options);

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
	public function text($text, $fontFile, $fontSize = 12, $color = true, $position = 'center', $options = array())
	{

		// check font
		if ((substr($fontFile, '1')!=='/') && !file_exists($fontFile) || !is_readable($fontFile)) {
			throw new Exception('Unable to load font: '.$fontFile);
		}
		
		if (!is_array($options)) {
			$options = array();
		}

		// additional options
		$opt = array_merge(array(
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
		), $options);

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
		$colorArr = array();

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
