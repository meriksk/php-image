<?php

declare(strict_types=1);

namespace meriksk\PhpImage\driver;

use Exception;
use meriksk\PhpImage\Image;
use meriksk\PhpImage\BaseImage;

if (!function_exists('imagepalettetotruecolor')) {
    function imagepalettetotruecolor(&$src)
    {
        if (imageistruecolor($src)) {
            return true;
        }

        $dst = imagecreatetruecolor(imagesx($src), imagesy($src));

        imagealphablending($dst, false);//prevent blending with default black
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);//change the RGB values if you need, but leave alpha at 127
        imagefilledrectangle($dst, 0, 0, imagesx($src), imagesy($src), $transparent);//simpler than flood fill
        imagealphablending($dst, true);//restore default blending

        imagecopy($dst, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
        imagedestroy($src);

        $src = $dst;
        return true;
    }
}

/**
 * ImageGd class file
 */
class ImageGd extends BaseImage
{
    protected static $driver = Image::DRIVER_GD;


    ////////////////////////////////////////////////////////////////////////////
    // Loaders
    ////////////////////////////////////////////////////////////////////////////


    /**
     * Load image resource
     * @param string $file Image path or the image data, as a string.
     * @return void
     * @throws Exception
     */
    protected function _loadFromFile(string $file): void
    {
        $this->debug("_loadFromFile('{$file}')");
        switch ($this->mime_type) {
            case 'image/jpeg':
                $this->resource = @imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $this->resource = imagecreatefrompng($file);
                if ($this->resource) {
                    imagealphablending($this->resource, false);
                    imagesavealpha($this->resource, true);
                }
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($file);

                if ($img) {
                    $w = imagesx($img);
                    $h = imagesy($img);
                    $this->resource = imagecreatetruecolor($w, $h);
                    $transparent = imagecolorallocatealpha($this->resource, 255, 255, 255, 127);
                    imagefill($this->resource, 0, 0, $transparent);
                    imagecolortransparent($this->resource, $transparent);
                    imagesavealpha($this->resource, true);
                    imagecopy($this->resource, $img, 0, 0, 0, 0, $w, $h);
                    imagedestroy($img);
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
    }

    protected function _ping(?string $filename = null): bool|null
    {
        $this->debug("");
        $this->debug("_ping('{$filename}')");

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

            // resource
        } elseif ($this->isResource()) {

            $this->w = imagesx($this->resource);
            $this->h = imagesy($this->resource);
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

        return null;
    }

    /**
     * Destroy an image resource
     * @param mixed $resource
     * @return void
     */
    protected function _destroy(mixed $resource): void
    {
        $this->debug("_destroy()");
        $resource = null;
    }

    /**
     * Save an image. The resulting format will be determined by the file extension.
     * @param string $filename
     * @param int $quality
     * @param string $mimeType
     * @return bool
     */
    protected function _save(string $filename, int $quality, string $mimeType): bool
    {
        switch ($mimeType) {
            case 'image/gif':
                return (bool)@imagegif($this->resource, $filename);
            case 'image/jpeg':
                return (bool)@imagejpeg($this->resource, $filename, $quality);
            case 'image/png':
                return (bool)@imagepng($this->resource, $filename, (int)round(9 * $quality / 100));
            default:
                return false;
        }
    }

    /**
     * Outputs image without saving
     * @param int $quality Output image quality in percents 0-100
     * @param string $mimeType
     * @return array|false
     * @throws Exception
     */
    protected function _output(int $quality, string $mimeType): array|false
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
                $result = imagepng($this->resource, null, (int)round(9 * $quality / 100));
                break;
            default:
                throw new Exception('Unsupported image format: ' . $mimeType);
        }

        $size = ob_get_length();
        $data = ob_get_clean();

        return $result ? [
            'mime_type' => $mimeType,
            'size' => $size,
            'data' => $data,
        ] : false;
    }

    /**
     * Resize an image to the specified dimensions
     * @param int $width
     * @param int $height
     * @param array|null $bgColor
     * @return void
     */
    protected function _resize(int $width, int $height, ?array $bgColor = null): void
    {
        $this->debug("_resize({$width}, {$height})");

        // Generate new GD image
        $img = imagecreatetruecolor($width, $height);

        switch ($this->mime_type) {
            case 'image/gif':
                $alpha = (int) ceil((1 - $bgColor['a']) * 127);
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);

                $transparentIndex = imagecolortransparent($this->resource);
                $palletsize = imagecolorstotal($this->resource);
                if ($transparentIndex >= 0 && $transparentIndex < $palletsize) {
                    $transparentColor = imagecolorsforindex($this->resource, $transparentIndex);
                    $transparentIndex = imagecolorallocate($img, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                    imagefill($img, 0, 0, $transparentIndex);
                    imagecolortransparent($img, $transparentIndex);
                }

                imagefill($img, 0, 0, $bgColor);
                break;

            case 'image/png':
                imagealphablending($img, true);
                imagesavealpha($img, true);
                $alpha = (int) ceil((1.0 - $bgColor['a']) * 127); // 127=transparent
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);
                imagefill($img, 0, 0, $bgColor);
                break;

            default:
                $bgColor = imagecolorallocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);
                imagefill($img, 0, 0, $bgColor);
                break;
        }

        // resize
        imagecopyresampled($img, $this->resource, 0, 0, 0, 0, $width, $height, $this->w, $this->h);
        imagedestroy($this->resource);

        // update meta data
        $this->resource = $img;
    }

    /**
     * Extracts a region of the image.
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param array|null $bgColor
     * @return void
     */
    protected function _crop(int $x, int $y, int $width, int $height, ?array $bgColor = null): void
    {
        $this->debug("_crop({$x}, {$y}, {$width}, {$height}, ". json_encode($bgColor) .")");

        // new image
        $img = imagecreatetruecolor($width, $height);

        switch ($this->mime_type) {
            case 'image/gif':
                $alpha = (int) ceil((1 - $bgColor['a']) * 127);
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);

                $transparentIndex = imagecolortransparent($this->resource);
                $palletsize = imagecolorstotal($this->resource);
                if ($transparentIndex >= 0 && $transparentIndex < $palletsize) {
                    $transparentColor = imagecolorsforindex($this->resource, $transparentIndex);
                    $transparentIndex = imagecolorallocate($img, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                    imagefill($img, 0, 0, $transparentIndex);
                    imagecolortransparent($img, $transparentIndex);
                }

                imagefill($img, 0, 0, $bgColor);
                break;

            case 'image/png':
                imagealphablending($img, true);
                imagesavealpha($img, true);
                $alpha = (int) ceil((1.0 - $bgColor['a']) * 127); // 127=transparent
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);
                imagefill($img, 0, 0, $bgColor);
                break;

            default:
                $bgColor = imagecolorallocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);
                imagefill($img, 0, 0, $bgColor);
                break;
        }

        imagecopy($img, $this->resource, 0, 0, $x, $y, $width, $height);
        imagedestroy($this->resource);
        $this->resource = $img;
    }

    /**
     * Thumbnail
     * @param int $width
     * @param int $height
     * @param bool $fill
     * @param bool $allowEnlarge
     * @param array|null $bgColor
     * @return void
     */
    protected function _thumbnail(int $width, int $height, bool $fill = false, bool $allowEnlarge = false, ?array $bgColor = null): void
    {
        $this->debug("_thumbnail($width, $height, ". ($fill === true ? 'true' : 'false').", ". ($allowEnlarge === true ? 'true' : 'false') .")");

        // new image
        $img = imagecreatetruecolor($width, $height);

        switch ($this->mime_type) {
            case 'image/gif':
                $alpha = (int) ceil((1 - $bgColor['a']) * 127);
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);

                $transparentIndex = imagecolortransparent($this->resource);
                $palletsize = imagecolorstotal($this->resource);
                if ($transparentIndex >= 0 && $transparentIndex < $palletsize) {
                    $transparentColor = imagecolorsforindex($this->resource, $transparentIndex);
                    $transparentIndex = imagecolorallocate($img, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                    imagefill($img, 0, 0, $transparentIndex);
                    imagecolortransparent($img, $transparentIndex);
                }

                imagefill($img, 0, 0, $bgColor);
                break;

            case 'image/png':
                imagealphablending($img, true);
                imagesavealpha($img, true);
                $alpha = (int) ceil((1.0 - $bgColor['a']) * 127); // 127=transparent
                $bgColor = imagecolorallocatealpha($img, $bgColor['r'], $bgColor['g'], $bgColor['b'], $alpha);
                imagefill($img, 0, 0, $bgColor);
                break;

            default:
                $bgColor = imagecolorallocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);
                imagefill($img, 0, 0, $bgColor);
                break;
        }

        // determine resize values
        $srcRatio = round($this->w / $this->h, 3);
        $dstRatio = round($width / $height, 3);

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

        $x = $this->round(($width - $this->w) / 2);
        $y = $this->round(($height - $this->h) / 2);

        imagecopy($img, $this->resource, (int)$x, (int)$y, 0, 0, $this->w, $this->h);
        $this->resource = $img;
    }

    /**
     * Flips an image using a given mode
     * @param string|int $mode
     * @return void
     */
    protected function _flip(string|int $mode): void
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
     * @param int $angle <p>Rotation angle, in degrees. The rotation angle is
     * interpreted as the number of degrees to rotate the image anticlockwise.</p>
     * @param array|null $bgColor <p>Specifies the color of the uncovered zone after the rotation</p>
     * Transparent by default.
     * @return void
     */
    public function _rotate(int $angle, ?array $bgColor = null): void
    {
        $this->debug("_rotate({$angle}, ". json_encode($bgColor) .")");

        // fix angle value
        $angle = $angle < 0 ? abs($angle) : 360 - $angle;
        $alpha = round((1 - $bgColor['a']) * 127);

        // perform the rotation
        if (in_array($this->mime_type, ['image/png', 'image/gif'])) {

            // transparent color
            $transparent = imagecolorallocatealpha($this->resource, 255, 255, 255, 127);

            // rotate, last parameter preserves alpha when true
            $rotated = imagerotate($this->resource, $angle, $transparent);
            imagedestroy($this->resource);

            if ($this->mime_type === 'image/png') {
                imagealphablending($rotated, false);
            }

            $w = imagesx($rotated);
            $h = imagesy($rotated);
            $new = imagecreatetruecolor($w, $h);
            imagesavealpha($new, true);

            $bgColor = imagecolorallocatealpha($new, $bgColor['r'], $bgColor['g'], $bgColor['b'], (int)$alpha);
            imagecolortransparent($new, $bgColor);
            imagefill($new, 0, 0, $bgColor);

            imagecopy($new, $rotated, 0, 0, 0, 0, $w, $h);
            $this->resource = $new;
            imagedestroy($rotated);
            $rotated = null;

        } else {
            $bg = imagecolorallocate($this->resource, $bgColor['r'], $bgColor['g'], $bgColor['b']);
            $this->resource = imagerotate($this->resource, $angle, $bg);
        }
    }

    /**
     * Set background color
     * @param array $color
     * @return void
     */
    protected function _setBackgroundColor(array $color): void
    {
        $img = imagecreatetruecolor($this->w, $this->h);

        // alpha
        if ($color['a'] < 1.0) {
            if ($this->mime_type === 'image/png') {
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }

            $alpha = (int) ceil((1 - $color['a']) * 127);
            $bgColor = imagecolorallocatealpha($img, $color['r'], $color['g'], $color['b'], $alpha);
            // no alpha
        } else {
            $bgColor = imagecolorallocate($img, $color['r'], $color['g'], $color['b']);
        }

        imagefill($img, 0, 0, $bgColor);
        imagecopy($img, $this->resource, 0, 0, 0, 0, $this->w, $this->h);
        $this->resource = $img;
    }

    /**
     * Reads the EXIF headers from an image file
     * @param string $file
     * @param string|null $requiredSections
     * @param bool $thumbnail
     * @return array|false
     */
    public function _readExifData(string $file, ?string $requiredSections = null, bool $thumbnail = false): array|false
    {
        if ($file && extension_loaded('exif')) {
            if ($requiredSections === null) {
                $requiredSections = 'FILE,COMPUTED,IFD0,COMMENT,EXIF,GPS';
            }

            $data = @exif_read_data($file, $requiredSections, false, $thumbnail);
            return is_array($data) ? $data : false;
        }

        return false;
    }

    /**
     * Add watermark to image resource
     * @param array $origin
     * @return bool
     */
    protected function _addImageWatermark(array $origin): bool
    {
        if ($this->resource && !empty(self::$watermark['image']) && !empty($origin)) {

            // Copy the stamp image onto our photo using the margin offsets and
            // the photo width to calculate positioning of the stamp.
            return imagecopy(
                $this->resource,
                self::$watermark['image'],
                $origin[0],
                $origin[1],
                0,
                0,
                self::$watermark['width'],
                self::$watermark['height']
            );
        }

        // default
        return false;
    }

    /**
     * Load watermark image into the memory
     * @param string $path
     * @return bool
     */
    protected function _loadImageWatermark(string $path): bool
    {
        // load image into memory
        $image = imagecreatefrompng($path);

        if ($image) {
            self::$watermark['image'] = $image;
            self::$watermark['width'] = imagesx($image);
            self::$watermark['height'] = imagesy($image);
            return true;
        }

        return false;
    }

    /**
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
     * @param Image|string $overlay An image path or an Image object
     * @param string $position center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param array $options
     *	  opacity int Overlay opacity 0-1
     *	  offsetX int Horizontal offset in pixels
     *	  offsetY int Vertical offset in pixels
     *	  maxWidth int Maximum overlay width
     *	  maxHeight int Maximum overlay height
     *
     * @return static
     */
    public function overlay(Image|string $overlay, string $position = 'center', array $options = []): static
    {
        // Load overlay image
        if (!($overlay instanceof Image)) {
            $overlay = Image::load($overlay);
        }

        // settings
        $settings = array_merge([
            'opacity' => 1,
            'offsetX' => null,
            'offsetY' => null,
            'maxWidth' => null,
            'maxHeight' => null,
        ], $options);

        // check settings
        $settings['opacity'] = is_numeric($settings['opacity']) ? intval($settings['opacity']) * 100 : 1;
        $settings['offsetX'] = is_numeric($settings['offsetX']) ? (int)$settings['offsetX'] : 0;
        $settings['offsetY'] = is_numeric($settings['offsetY']) ? (int)$settings['offsetY'] : 0;
        $settings['maxWidth'] = ($settings['maxWidth'] > 0) ? (int)$settings['maxWidth'] : $this->w;
        $settings['maxHeight'] = ($settings['maxHeight'] > 0) ? (int)$settings['maxHeight'] : $this->h;


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
                $x = $this->w - $overlay->width + $settings['offsetX'];
                $y = 0 + $settings['offsetY'];
                break;
            case 'top':
            case 'top center':
                $x = ($this->w / 2) - ($overlay->width / 2) + $settings['offsetX'];
                $y = 0 + $settings['offsetY'];
                break;
            case 'bottom left':
                $x = 0 + $settings['offsetX'];
                $y = $this->h - $overlay->height + $settings['offsetY'];
                break;
            case 'bottom right':
                $x = $this->w - $overlay->width + $settings['offsetX'];
                $y = $this->h - $overlay->height + $settings['offsetY'];
                break;
            case 'bottom':
            case 'bottom center':
                $x = ($this->w / 2) - ($overlay->width / 2) + $settings['offsetX'];
                $y = $this->h - $overlay->height + $settings['offsetY'];
                break;
            case 'left':
                $x = 0 + $settings['offsetX'];
                $y = ($this->h / 2) - ($overlay->height / 2) + $settings['offsetY'];
                break;
            case 'right':
                $x = $this->w - $overlay->width + $settings['offsetX'];
                $y = ($this->h / 2) - ($overlay->height / 2) + $settings['offsetY'];
                break;
            case 'center':
            case 'center center':
            default:
                $x = ($this->w / 2) - ($overlay->width / 2) + $settings['offsetX'];
                $y = ($this->h / 2) - ($overlay->height / 2) + $settings['offsetY'];
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
     * @return static
     * @throws Exception
     */
    public function text(string $text, string $fontFile, float|int $fontSize = 12, mixed $color = true, string $position = 'center', array $options = []): static
    {

        // check font
        if ((substr($fontFile, '1') !== '/') && !file_exists($fontFile) || !is_readable($fontFile)) {
            throw new Exception('Unable to load font: '.$fontFile);
        }

        // additional options
        $opt = array_merge([
            'x' => null,
            'y' => null,
            'offsetX' => 0,
            'offsetY' => 0,
            'strokeColor' => null,
            'strokeSize' => null,
            'alignment' => null,
            'letterSpacing' => 0,
            'angle' => 0,
        ], $options);

        // determine textbox size
        $box = imagettfbbox($fontSize, $opt['angle'], $fontFile, $text);

        $boxWidth = abs($box[2] - $box[0]);
        $boxHeight = abs($box[7] - $box[1]);

        // downsize font by 20%
        if ($boxWidth >= $this->w) {
            $fontSize = $fontSize - ((20 * $fontSize) / 100);
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
                    $y = 0 + $opt['offsetY'];
                    break;
                case 'top right':
                    $x = $this->w - $boxWidth + $opt['offsetX'];
                    $y = 0 + $opt['offsetY'];
                    break;
                case 'top':
                case 'top center':
                    $x = ($this->w / 2) - ($boxWidth / 2) + $opt['offsetX'];
                    $y = 0 + $opt['offsetY'];
                    break;
                case 'bottom left':
                    $x = 0 + $opt['offsetX'];
                    $y = $this->h - $boxHeight + $opt['offsetY'] + $boxHeight;
                    break;
                case 'bottom right':
                    $x = $this->w - $boxWidth + $opt['offsetX'];
                    $y = $this->h - $boxHeight + $opt['offsetY'] + $boxHeight;
                    break;
                case 'bottom':
                case 'bottom center':
                    $x = ($this->w / 2) - ($boxWidth / 2) + $opt['offsetX'];
                    $y = $this->h - $boxHeight + $opt['offsetY'] + $boxHeight;
                    break;
                case 'left':
                    $x = 0 + $opt['offsetX'];
                    $y = ($this->h / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
                    break;
                case 'right':
                    $x = $this->w - $boxWidth + $opt['offsetX'];
                    $y = ($this->h / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
                    break;
                case 'center':
                case 'center center':
                default:
                    $x = ($this->w / 2) - ($boxWidth / 2) + $opt['offsetX'];
                    $y = ($this->h / 2) - (($boxHeight / 2) - $boxHeight) + $opt['offsetY'];
                    break;
            }

            // Left aligned text
            if ($opt['alignment'] === 'left') {
                $x = -($x * 2);
                // Right aligned text
            } elseif ($opt['alignment'] === 'right') {
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
                ((strlen(dechex($rgba['red'])) === 1) ? dechex($rgba['red']) : '') . dechex($rgba['red']) .
                ((strlen(dechex($rgba['green'])) === 1) ? dechex($rgba['green']) : '') . dechex($rgba['green']) .
                ((strlen(dechex($rgba['blue'])) === 1) ? dechex($rgba['blue']) : '') . dechex($rgba['blue']);

            $rgba = Image::normalizeColor(Image::oppositeColor($color));
            $colorArr[] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        } elseif (is_array($color)) {

            foreach ($color as $var) {
                $rgba = Image::normalizeColor($var);
                $colorArr[] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
            }

        } else {

            $rgba = Image::normalizeColor($color);
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
                        $rgba = Image::normalizeColor($opt['strokeColor'][$key]);
                        $opt['strokeColor'][$key] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
                    }
                } else {
                    $rgba = Image::normalizeColor($opt['strokeColor']);
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
                $rgba = Image::normalizeColor($opt['strokeColor']);
                $opt['strokeColor'] = imagecolorallocatealpha($this->resource, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
                $this->imagettfstroketext($this->resource, $fontSize, $opt['angle'], $x, $y, $colorArr[0], $opt['strokeColor'], $opt['strokeSize'], $fontFile, $text);
            }
        } else {
            // Text without stroke
            if (is_array($color)) {
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
     * @return static
     */
    public function blur(string $type = 'selective', int $passes = 1): static
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
     * @return static
     */
    public function brightness(int $level): static
    {
        imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, $this->keepWithin($level, -255, 255));
        return $this;
    }

    /**
     * Contrast
     * @param int $level  Min = -100, max = 100
     * @return static
     */
    public function contrast(int $level): static
    {
        imagefilter($this->resource, IMG_FILTER_CONTRAST, $this->keepWithin($level, -100, 100));
        return $this;
    }

    /**
     * Colorize
     * @param string|array $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float|int $opacity 0-1
     * @return static
     */
    public function colorize(string|array $color, float|int $opacity): static
    {
        $rgba = Image::normalizeColor($color);
        $alpha = $this->keepWithin(127 - (127 * $opacity), 0, 127);
        imagefilter($this->resource, IMG_FILTER_COLORIZE, $this->keepWithin($rgba['r'], 0, 255), $this->keepWithin($rgba['g'], 0, 255), $this->keepWithin($rgba['b'], 0, 255), $alpha);
        return $this;
    }

    /**
     * Desaturate
     * @param int $percentage Level of desaturization.
     * @return static
     */
    public function desaturate(int $percentage = 100): static
    {
        // Determine percentage
        $percentage = $this->keepWithin($percentage, 0, 100);
        if ($percentage === 100) {
            imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        } else {
            // Make a desaturated copy of the image
            $new = imagecreatetruecolor($this->w, $this->h);
            imagealphablending($new, false);
            imagesavealpha($new, true);
            imagecopy($new, $this->resource, 0, 0, 0, 0, $this->w, $this->h);
            imagefilter($new, IMG_FILTER_GRAYSCALE);
            // Merge with specified percentage
            $this->imagecopymergeAlpha($this->resource, $new, 0, 0, 0, 0, $this->w, $this->h, $percentage);
            imagedestroy($new);
        }

        return $this;
    }

    /**
     * Edge Detect
     * @return static
     */
    public function edges(): static
    {
        imagefilter($this->resource, IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * Emboss
     * @return static
     */
    public function emboss(): static
    {
        imagefilter($this->resource, IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * Invert
     * @return static
     */
    public function invert(): static
    {
        imagefilter($this->resource, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Mean Remove
     * @return static
     */
    public function meanRemove(): static
    {
        imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Pixelate
     * @param int $blockSize Size in pixels of each resulting block
     * @return static
     */
    public function pixelate(int $blockSize = 10): static
    {
        imagefilter($this->resource, IMG_FILTER_PIXELATE, $blockSize, true);
        return $this;
    }

    /**
     * Sepia effect
     * @return static
     */
    public function sepia(): static
    {
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        imagefilter($this->resource, IMG_FILTER_COLORIZE, 100, 50, 0);
        return $this;
    }

    /**
     * Sketch
     * @return static
     */
    public function sketch(): static
    {
        imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Smooth
     * @param int $level  Min = -10, max = 10
     * @return static
     */
    public function smooth(int $level): static
    {
        imagefilter($this->resource, IMG_FILTER_SMOOTH, $this->keepWithin($level, -10, 10));
        return $this;
    }

    /**
     * Copy and merge part of an image
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     *
     * @param \GdImage $dst_im
     * @param \GdImage $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @param int $pct
     * @return void
     */
    protected function imagecopymergeAlpha(\GdImage $dst_im, \GdImage $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct): void
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
     * Same as imagettftext(), but allows for a stroke color and size
     *
     * @param \GdImage $image A GD image object
     * @param float|int $size The font size
     * @param float|int $angle The angle in degrees
     * @param int $x X-coordinate of the starting position
     * @param int $y Y-coordinate of the starting position
     * @param int $textcolor The color index of the text
     * @param int $strokecolor The color index of the stroke
     * @param int $stroke_size The stroke size in pixels
     * @param string $fontfile The path to the font to use
     * @param string $text The text to output
     *
     * @return array|false
     */
    protected function imagettfstroketext(\GdImage $image, float|int $size, float|int $angle, int $x, int $y, int $textcolor, int $strokecolor, int $stroke_size, string $fontfile, string $text): array|false
    {
        for ($c1 = ($x - abs($stroke_size)); $c1 <= ($x + abs($stroke_size)); $c1++) {
            for ($c2 = ($y - abs($stroke_size)); $c2 <= ($y + abs($stroke_size)); $c2++) {
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
            }
        }

        return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }

}
