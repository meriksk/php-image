<?php

declare(strict_types=1);

namespace meriksk\PhpImage;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use meriksk\PhpImage\DriverFactory;

/**
 * Image class file
 */
class Image
{
    public const COLOR_TRANSPARENT = -1;
    public const COLOR_WHITE = 'FFFFFF';
    public const COLOR_BLACK = '000000';

    public const DRIVER_GD = 'gd';
    public const DRIVER_IMAGICK = 'imagick';

    public const ORIENTATION_LANDSCAPE = 'landscape';
    public const ORIENTATION_PORTRAIT = 'portrait';
    public const ORIENTATION_SQUARE = 'square';

    public const CROP_CENTER = 1;
    public const CROP_LEFT = 2;
    public const CROP_RIGHT = 3;
    public const CROP_TOP = 4;
    public const CROP_BOTTOM = 5;
    public const CROP_TOP_LEFT = 6;
    public const CROP_TOP_RIGHT = 7;
    public const CROP_BOTTOM_LEFT = 8;
    public const CROP_BOTTOM_RIGHT = 9;

    public const FLIP_HORIZONTAL = 'horizontal';
    public const FLIP_VERTICAL = 'vertical';
    public const FLIP_BOTH = 'both';

    public static $enableAutoRotate = true;
    public static $allowUpscale = false;
    public static $debug = false;
    public static $test;

    public $autoRotate;

    /** @var \meriksk\PhpImage\BaseImage */
    public $driver;


    /**
     * Class constructor
     * @param string|null $filename
     * @param string|null $driver
     * @param array $options
     * @throws Exception
     */
    public function __construct(?string $filename = null, ?string $driver = null, array $options = [])
    {
        // init driver
        $this->driver = DriverFactory::get($driver);

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                } else {
                    throw new Exception('Unsetting an unknown or read-only property: ' . get_class($this) . '::' . $key);
                }
            }
        }

        // load image
        if ($filename !== null) {
            $this->driver->autoRotate = !is_null($this->autoRotate) ? $this->autoRotate === true : self::$enableAutoRotate;
            $this->driver->loadFromFile($filename);
        }
    }

    /**
     * Load an image
     * @param string|null $driver
     * @return static
     * @throws Exception
     */
    public static function getInstance(?string $driver = null): static
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

    public function __toString(): string
    {
        return (string)$this->driver->toString(100);
    }

    /**
     * Returns driver used by script
     * @return BaseImage
     */
    public function getDriver(): BaseImage
    {
        return $this->driver;
    }

    /**
     * Returns driver name used by script
     * @return string
     */
    public function getDriverName(): string
    {
        if (strpos(get_class($this->driver), 'DriverGd') !== false) {
            return self::DRIVER_GD;
        } else {
            return self::DRIVER_IMAGICK;
        }
    }

    /**
     * Loads an image from a file.
     *
     * @param string $file The image file to load.
     * @param string|null $driver Image library driver
     * @return static
     * @throws \Exception Thrown if file or image data is invalid.
     */
    public static function load(string $file, ?string $driver = null): static
    {
        $image = new Image(null, $driver);
        $image->driver->loadFromFile($file);
        return $image;
    }

    /**
     * Loads an image from a file.
     *
     * @param string $file The image file to load.
     * @return static
     * @throws \Exception Thrown if file or image data is invalid.
     */
    public function loadFromFile(string $file): static
    {
        $this->driver->loadFromFile($file);
        return $this;
    }

    /**
     * Loads an image from a file.
     *
     * @param string $file The image file to load.
     * @param string|null $driver Image library driver
     * @return static
     * @throws \Exception Thrown if file or image data is invalid.
     */
    public static function fromFile(string $file, ?string $driver = null): static
    {
        $image = new Image(null, $driver);
        $image->driver->loadFromFile($file);
        return $image;
    }

    /**
     * Loads an image from a string.
     *
     * @param string $data The raw image data as a string.
     * @return static
     * @throws \Exception Thrown if file or image data is invalid.
     */
    public function loadFromString(string $data): static
    {
        $this->driver->loadFromString($data, true);
        return $this;
    }

    /**
     * Creates a new image from a string.
     * @param string $data The raw image data as a string.
     * @param string|null $driver Image library driver
     * @return static
     */
    public static function fromString(string $data, ?string $driver = null): static
    {
        $image = new Image(null, $driver);
        $image->driver->loadFromString($data, true);
        return $image;
    }

    /**
     * Creates a new image from a base64 encoded string.
     * @param string $data The raw image data encoded as a base64 string.
     * @return static
     */
    public function loadFromBase64(string $data): static
    {
        $this->driver->loadFromString($data, false);
        return $this;
    }

    /**
     * Creates a new image from a base64 encoded string.
     * @param string $data The raw image data encoded as a base64 string.
     * @param string|null $driver Image library driver
     * @return static
     */
    public static function fromBase64(string $data, ?string $driver = null): static
    {
        $image = new Image(null, $driver);
        $image->driver->loadFromString($data, false);
        return $image;
    }

    /**
     * Fetch basic attributes about the image.
     * @param string|null $filename
     * @return array
     * @throws Exception
     */
    public function ping(?string $filename = null): array
    {
        return $this->driver->ping($filename);
    }

    /**
     * Fetch basic attributes about the image.
     * @param string $filename
     * @param string|null $driver Image library driver
     * @return array
     * @throws Exception
     */
    public static function pingImage(string $filename, ?string $driver = null): array
    {
        $image = new Image(null, $driver);
        return $image->driver->ping($filename);
    }

    /**
     * Get image info
     * @param bool $extendedInfo Read extended information about the image (Exif, Gps, ...)
     * @return array
     */
    public function getInfo(bool $extendedInfo = false): array
    {
        return $this->driver->getInfo($extendedInfo);
    }

    /**
     * Destroy image resources
     * @return static
     */
    public function destroy(): static
    {
        $this->driver->destroy();
        return $this;
    }

    /**
     * Revert an image
     * @return static
     */
    public function revert(): static
    {
        $this->driver->revert();
        return $this;
    }

    /**
     * Returns image resource
     * @return mixed
     */
    public function getResource(): mixed
    {
        return $this->driver->resource;
    }

    /**
     * Returns image path
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->driver->path;
    }

    /**
     * Returns the file extension of the specified file
     * @param string|null $path
     * @return string
     */
    public function getExtensionFromPath(?string $path = null): string
    {
        if ($path === null) {
            $path = $this->driver->path;
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Returns image dimensions
     * @return array|false Returns image dimensions or false on errors.
     */
    public function getDimensions(): array|false
    {
        return $this->driver->getDimensions();
    }

    /**
     * Returns image width
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->driver->w;
    }

    /**
     * Returns image height
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->driver->h;
    }

    /**
     * Returns the MIME content type
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->driver->mime_type;
    }

    /**
     * Returns image extension
     * @param bool $dot
     * @return string|null
     */
    public function getExtension(bool $dot = false): ?string
    {
        return $this->driver->extension ? ($dot === true ? '.' : '') . $this->driver->extension : null;
    }

    /**
     * Get image orientation
     * @return string|null
     */
    public function getOrientation(): ?string
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
     * @param string|int $imageType
     * @return bool
     */
    public function isImageType(string|int $imageType): bool
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
    public function isJpeg(): bool
    {
        return $this->isImageType('image/jpeg');
    }

    /**
     * Checks if an image is in PNG format.
     * @return bool
     */
    public function isPng(): bool
    {
        return $this->isImageType('image/png');
    }

    /**
     * Checks if an image is in GIF format.
     * @return bool
     */
    public function isGif(): bool
    {
        return $this->isImageType('image/gif');
    }

    /**
     * Save an image. The resulting format will be determined by the file extension.
     * @param string|null $filename If omitted - original file will be overwritten
     * @param int|null $quality Output image quality in percents 0-100
     * @param string|null $imageType The image type to use; determined by file extension if null
     * @return bool
     * @throws Exception
     */
    public function save(?string $filename = null, ?int $quality = null, ?string $imageType = null): bool
    {
        return $this->driver->save($filename, $quality, $imageType);
    }

    /**
     * Generates an image string.
     * @param int $quality Image quality as a percentage (default 100).
     * @param string|null $imageType The image format to output as a mime type (defaults to the original mime type).
     * @return string|false
     */
    public function toString(int $quality = 100, ?string $imageType = null): string|false
    {
        return $this->driver->toString($quality, $imageType);
    }

    /**
     * Generates a base64 string.
     * @param int $quality Image quality as a percentage (default 100).
     * @param string|null $imageType The image format to output as a mime type (defaults to the original mime type).
     * @return string|false
     */
    public function toBase64(int $quality = 100, ?string $imageType = null): string|false
    {
        return $this->driver->toBase64($quality, $imageType);
    }

    /**
     * Generates a data URI.
     * @param int $quality Image quality as a percentage (default 100).
     * @param string|null $imageType The image format to output as a mime type (defaults to the original mime type).
     * @return string|false Returns a string containing a data URI.
     */
    public function toDataUri(int $quality = 100, ?string $imageType = null): string|false
    {
        return $this->driver->toDataUri($quality, $imageType);
    }

    /**
     * Outputs the image to the screen. Must be called before any output is sent to the screen.
     * @param int|null $quality Output image quality in percents 0-100
     * @param string|null $imageType If omitted or null - image type of original file will be used (extension or mime-type)
     * @return void
     */
    public function toScreen(?int $quality = null, ?string $imageType = null): void
    {
        $this->driver->toScreen($quality ?? 100, $imageType);
    }

    /**
     * Outputs the image to the browser as an attachment to be downloaded to local storage.
     * @param string|null $filename If omitted - original file will be used
     * @param int|null $quality Output image quality in percents 0-100
     * @param string|null $imageType If omitted or null - image type of original file will be used (extension or mime-type)
     * @return void
     */
    public function download(?string $filename = null, ?int $quality = null, ?string $imageType = null): void
    {
        $this->driver->download($filename, $quality ?? 100, $imageType);
    }

    /**
     * Resize an image to the specified dimensions
     * @param int $width Desired width
     * @param int $height Desired height
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resize(int $width, int $height, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resize($width, $height, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given width (height proportional)
     * @param int $width
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToWidth(int $width, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resizeToWidth($width, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given height (width proportional)
     * @param int $height
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToHeight(int $height, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resizeToHeight($height, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given short side (long side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToShortSide(int $max, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resizeToShortSide($max, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image according to the given long side (short side proportional)
     * @param int $max
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToLongSide(int $max, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resizeToLongSide($max, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image to best fit inside the given dimensions
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToBestFit(int $maxWidth, int $maxHeight, bool $allowEnlarge = true, string|array|null $bgColor = null): static
    {
        $this->driver->resizeToBestFit($maxWidth, $maxHeight, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Resizes image to worst fit inside the given dimensions
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $allowEnlarge
     * @param string|array|null $bgColor
     * @return static
     */
    public function resizeToWorstFit(int $maxWidth, int $maxHeight, bool $allowEnlarge = true, string|array|null $bgColor = null): static
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
     * @param string|array|null $bgColor
     * @return static
     */
    public function crop(int $x, int $y, int $width, int $height, bool $allowEnlarge = false, string|array|null $bgColor = null): static
    {
        $this->driver->crop($x, $y, $width, $height, $allowEnlarge, $bgColor);
        return $this;
    }

    /**
     * Crops image according to the given width, height and crop position.
     * @param int $width
     * @param int $height
     * @param int $position
     * @param string|array|null $bgColor
     * @return static
     */
    public function cropAuto(int $width, int $height, int $position = self::CROP_CENTER, string|array|null $bgColor = null): static
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
     * @param string|array|null $bgColor
     * @return static
     * @throws Exception
     */
    public function thumbnail(int $width, int $height, bool $fill = false, bool $enlarge = true, string|array|null $bgColor = null): static
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
     * @param string $mode
     * @return static
     * @throws Exception
     */
    public function flip(string $mode = self::FLIP_VERTICAL): static
    {
        if (!in_array($mode, [Image::FLIP_HORIZONTAL, Image::FLIP_VERTICAL, Image::FLIP_BOTH])) {
            throw new InvalidArgumentException('Invalid flip mode.');
        }

        $this->driver->flip($mode);
        return $this;
    }

    /**
     * Rotates an image.
     * @param int $angle Rotation angle in degrees. Supports negative values.
     * @param string|array|null $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * Transparent by default.
     * @return static
     */
    public function rotate(int $angle, string|array|null $bgColor = null): static
    {
        if (!is_numeric($angle) || $angle < -360 || $angle > 360) {
            throw new InvalidArgumentException('Angle must be a valid number between -360 and 360.');
        }

        $this->driver->rotate($angle, $bgColor);
        return $this;
    }

    /**
     * Auto-adjust image rotation based on EXIF 'orientation'
     * @return static
     */
    public function autoRotate(): static
    {
        $this->driver->autoRotate();
        return $this;
    }

    /**
     * Set background color
     * @param string|array $color
     * @return static
     */
    public function setBackgroundColor(string|array $color): static
    {
        $this->driver->setBackgroundColor($color);
        return $this;
    }

    /**
     * Normalize color
     * @param string|array|null $color
     * @param string|array|null $defaultColor
     * @return array|string
     */
    public static function normalizeColor(string|array|null $color, string|array|null $defaultColor = null): array|string
    {
        if ($color === null) {
            return ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0.0];
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
        return $color;
    }

    /**
     * Opposite color
     * @param string|array $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param bool $inverse
     * @return string
     */
    public static function oppositeColor(string|array $color, bool $inverse = false): string
    {
        // sharp
        $sharp = (is_string($color) && strpos($color, '#') === 0);

        // normalize color
        $color = self::normalizeColor($color);

        // inversed color
        if ($inverse) {
            $r = (strlen($r = dechex(255 - $color['r'])) < 2) ? '0'.$r : $r;
            $g = (strlen($g = dechex(255 - $color['g'])) < 2) ? '0'.$g : $g;
            $b = (strlen($b = dechex(255 - $color['b'])) < 2) ? '0'.$b : $b;
            return ($sharp ? '#' : '') . $r.$g.$b;
            // monotone based on darkness of original
        } else {
            return ($sharp ? '#' : '') . ((array_sum($color) > (255 * 1.5)) ? '000000' : 'FFFFFF');
        }
    }

    /**
     * Convert HEX value to it's color percentage representation (00 => 0%, 7F => 50%, FF => 100%)
     * @param string $hex value (00-FF)
     * @param bool $floating
     * @return float|int
     */
    public static function hex2percentage(string $hex, bool $floating = false): float|int
    {
        if (is_string($hex) && strlen($hex) === 2) {
            $val = hexdec($hex) / 255;
            return $floating === true ? round($val, 2) : round($val * 100);
        }

        return 100;
    }

    /**
     * Convert percentage value to it's HEX color representation (0% => 00, 50% => 7F, 100% => FF)
     * @param int|float $percentage value (0 - 100 or 0.0 - 1.0)
     * @return string
     */
    public static function percentage2hex(int|float $percentage): string
    {
        if (is_numeric($percentage)) {
            if ($percentage > 1) {
                $percentage = $percentage / 100;
            }

            $val = (int) ceil($percentage * 255);
            return str_pad(dechex($val), 2, '0', STR_PAD_LEFT);
        }

        return 'FF';
    }

    /**
     * Converts a HEX color value to its RGB equivalent
     * @param string|array $color
     * Where red, green, blue - ints 0-255, alpha - int 0-255
     * @return array
     */
    public static function hex2rgba(string|array $color): array
    {
        // default color (transparent)
        $r = 255;
        $g = 255;
        $b = 255;
        $a = 1.0;

        if (is_string($color)) {
            $color = trim(strtolower($color), '#');

            switch ($color) {

                // black
                case 'black':
                case '000':
                case '000000':
                case '000000ff':
                case self::COLOR_BLACK:
                    list($r, $g, $b, $a) = [0, 0, 0, 1.0];
                    break;

                    // white
                case 'white':
                case 'fff':
                case 'ffffff':
                case 'ffffffff':
                case self::COLOR_WHITE:
                    list($r, $g, $b, $a) = [255, 255, 255, 1.0];
                    break;

                    // transparent
                case 'transparent':
                case '00000000':
                case 'ffffff00':
                case self::COLOR_TRANSPARENT:
                    list($r, $g, $b, $a) = [255, 255, 255, 0.0];
                    break;

                default:
                    // 8-digit hexadecimal notation (with alpha)
                    if (strlen($color) === 8) {
                        list($r, $g, $b, $a) = [hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]), self::hex2percentage($color[6].$color[7], true)];
                        // 6-digit hexadecimal notation (no alpha)
                    } elseif (strlen($color) === 6) {
                        list($r, $g, $b) = [hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5])];
                        // 4-digit hexadecimal notation
                    } elseif (strlen($color) === 4) {
                        list($r, $g, $b, $a) = [hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]), self::hex2percentage($color[3].$color[3], true)];
                        // 3-digit hexadecimal notation (no alpha).
                    } elseif (strlen($color) === 3) {
                        list($r, $g, $b) = [hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2])];
                    }
                    break;
            }

        } elseif (is_array($color)) {
            $r = isset($color[0]) && $color[0] >= 0 && $color[0] <= 255 ? $color[0] : 0;
            $g = isset($color[1]) && $color[1] >= 0 && $color[1] <= 255 ? $color[1] : 0;
            $b = isset($color[2]) && $color[2] >= 0 && $color[2] <= 255 ? $color[2] : 0;
            $a = isset($color[3]) && $color[3] >= 0 && $color[3] <= 1 ? (float)$color[3] : 0.0;
        }

        // rgba value
        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a];
    }

    /**
     * Converts a RGB color value to its HEX equivalent
     * @param array $color
     * @param bool $returnArray
     * @return string|false
     */
    public static function rgb2hex(array $color, bool $returnArray = false): string|false
    {
        return self::rgba2hex($color, $returnArray);
    }

    /**
     * Converts a RGBA color value to its HEX equivalent
     * @param array $color
     * @param bool $returnArray
     * @return string|false
     */
    public static function rgba2hex(array $color, bool $returnArray = false): string|false
    {
        $hex = false;
        if ($color && is_array($color)) {
            $c = [];
            $alpha = null;

            if (isset($color['r'])) {
                $c[0] = isset($color['r']) ? $color['r'] : 0;
                $c[1] = isset($color['g']) ? $color['g'] : 0;
                $c[2] = isset($color['b']) ? $color['b'] : 0;
                if (isset($color['a'])) {
                    $alpha = $color['a'];
                }
            } else {
                $c[0] = isset($color[0]) ? $color[0] : 0;
                $c[1] = isset($color[1]) ? $color[1] : 0;
                $c[2] = isset($color[2]) ? $color[2] : 0;
                if (isset($color[3])) {
                    $alpha = $color[3];
                }
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
     * @param string|null $file
     * @param string|null $requiredSections
     * @param bool $thumbnail
     * @return array|false
     */
    public function readExifData(?string $file = null, ?string $requiredSections = null, bool $thumbnail = false): array|false
    {
        return $this->driver->readExifData($file, $requiredSections, $thumbnail);
    }

    /**
     * Get image EXIF property
     * @param string|array|null $property
     * @param mixed $default
     * @return mixed
     */
    public function getExifData(string|array|null $property = null, mixed $default = null): mixed
    {
        return $this->driver->getExifData($property, $default);
    }

    /**
     * Returns image date of creation
     * @return DateTimeImmutable|null
     */
    public function getDateCreated(): DateTimeImmutable
    {
        return $this->driver->getDateCreated();
    }

    /**
     * Returns GPS coordinates from EXIF data
     * @param bool|null $dmsFormat
     * @return array|null
     */
    public function getGps(?bool $dmsFormat = null): ?array
    {
        return $this->driver->getGps($dmsFormat);
    }

    /**
     * Replace accented characters with non accented
     * @param string $string
     * @return string
     */
    public function removeAccents(string $string): string
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
    public function setWatermark(string $path, array $config = []): bool
    {
        // already loaded
        if (BaseImage::$watermark !== null) {
            return BaseImage::$watermark !== false;
        }

        if ($path && !file_exists($path)) {
            BaseImage::$watermark = false;
            return false;
        }

        // load
        $cfg = is_array($config) ? $config : [];

        BaseImage::$watermark = [
            'width' => 0,
            'height' => 0,
            'position' => (!empty($cfg['position']) ? $cfg['position'] : 'middle-bottom'),
            'offsetX' => (!empty($cfg['offsetX']) ? $cfg['offsetX'] : 0),
            'offsetY' => (!empty($cfg['offsetY']) ? $cfg['offsetY'] : -15),
            'image' => null,
        ];

        return $this->driver->loadImageWatermark($path);
    }

    /**
     * Add watermark to image resource
     * @return void
     */
    public function addWatermark(): void
    {
        if (BaseImage::$watermark && $this->driver->resource) {
            $origin = $this->alignWatermark(
                BaseImage::$watermark['position'],
                BaseImage::$watermark['width'],
                BaseImage::$watermark['height'],
                $this->driver->w,
                $this->driver->h,
                BaseImage::$watermark['offsetX'],
                BaseImage::$watermark['offsetY']
            );

            if (is_array($origin)) {
                $this->driver->addImageWatermark($origin);
            }
        }
    }

    /**
     * Align watermark stamp
     *
     * @param string $position
     * @param int $stampWidth
     * @param int $stampHeight
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @param int $offsetX Watermark X offset
     * @param int $offsetY Watermark Y offset
     * @return array|false|null
     */
    protected function alignWatermark(string $position, int $stampWidth, int $stampHeight, int $sourceWidth, int $sourceHeight, int $offsetX = 0, int $offsetY = 0): array|false|null
    {
        if (!empty($position) && ($stampWidth > 0) && ($stampHeight > 0) && ($sourceWidth > 0) && ($sourceHeight > 0)) {

            if (($stampWidth > $sourceWidth) or ($stampHeight > $sourceHeight)) {
                return false;
            }

            $x = $y = 0;

            switch ($position) {

                case 'left-top':
                    $x = $y = 0;
                    break;
                case 'left-middle':
                    $x = 0;
                    $y = ($sourceHeight / 2) - ($stampHeight / 2);
                    break;
                case 'left-bottom':
                    $x = 0;
                    $y = $sourceHeight - $stampHeight;
                    break;
                case 'middle-top':
                    $x = ($sourceWidth / 2) - ($stampWidth / 2);
                    $y = 0;
                    break;
                case 'center':
                    $x = ($sourceWidth / 2) - ($stampWidth / 2);
                    $y = ($sourceHeight / 2) - ($stampHeight / 2);
                    break;
                case 'middle-bottom':
                    $x = ($sourceWidth / 2) - ($stampWidth / 2);
                    $y = $sourceHeight - $stampHeight;
                    break;
                case 'right-top':
                    $x = $sourceWidth - $stampWidth;
                    $y = 0;
                    break;
                case 'right-middle':
                    $x = $sourceWidth - $stampWidth;
                    $y = ($sourceHeight / 2) - ($stampHeight / 2);
                    break;
                case 'right-bottom':
                    $x = $sourceWidth - $stampWidth;
                    $y = $sourceHeight - $stampHeight;
                    break;
                default:
                    return false;
            }

            // offset
            $x = $x + $offsetX;
            $y = $y + $offsetY;

            return [$x, $y];
        }

        return null;
    }

}
