<?php

namespace meriksk\PhpImage\Tests\Image;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use meriksk\PhpImage\Tests\BaseTestCase;
use meriksk\PhpImage\Image;

class ImageTestCase extends BaseTestCase
{
    /** @var Image|null */
    private $landscape = null;

    /** @var Image|null */
    private $portrait = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->landscape = new Image(static::$imageLandscape, static::$lib);
        $this->portrait  = new Image(static::$imagePortrait, static::$lib);
    }

    protected function tearDown(): void
    {
        if ($this->landscape !== null) {
            $this->landscape->destroy();
        }
        if ($this->portrait !== null) {
            $this->portrait->destroy();
        }
        parent::tearDown();
    }


    // -------------------------------------------------------------------------
    // Construction / loading
    // -------------------------------------------------------------------------

    public function testConstruct(): void
    {
        // path
        $image = new Image(self::$imageLandscape, static::$lib);
        $this->assertImage($image);

        // data url
        $image = new Image('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gIcSUNDX1BST0ZJTEUAAQEAAAIMbGNtcwIQAABtbnRyUkdCIFhZWiAH3AABABkAAwApADlhY3NwQVBQTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA9tYAAQAAAADTLWxjbXMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAApkZXNjAAAA/AAAAF5jcHJ0AAABXAAAAAt3dHB0AAABaAAAABRia3B0AAABfAAAABRyWFlaAAABkAAAABRnWFlaAAABpAAAABRiWFlaAAABuAAAABRyVFJDAAABzAAAAEBnVFJDAAABzAAAAEBiVFJDAAABzAAAAEBkZXNjAAAAAAAAAANjMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB0ZXh0AAAAAEZCAABYWVogAAAAAAAA9tYAAQAAAADTLVhZWiAAAAAAAAADFgAAAzMAAAKkWFlaIAAAAAAAAG+iAAA49QAAA5BYWVogAAAAAAAAYpkAALeFAAAY2lhZWiAAAAAAAAAkoAAAD4QAALbPY3VydgAAAAAAAAAaAAAAywHJA2MFkghrC/YQPxVRGzQh8SmQMhg7kkYFUXdd7WtwegWJsZp8rGm/fdPD6TD////bAIQAAgICAgICAwMDAwQEAwQEBQUEBAUFCAYGBgYGCAwHCQcHCQcMCw0KCgoNCxMPDQ0PExYSERIWGhgYGiEgISwsOwECAgICAgIDAwMDBAQDBAQFBQQEBQUIBgYGBgYIDAcJBwcJBwwLDQoKCg0LEw8NDQ8TFhIREhYaGBgaISAhLCw7/8IAEQgAZABkAwEiAAIRAQMRAf/EADgAAQACAQUBAAAAAAAAAAAAAAAHCQgBAgMEBQYBAQABBQEBAQAAAAAAAAAAAAAIBAUGBwkBAgP/2gAMAwEAAhADEAAAAJgE6ON41NGzbZNgcrzPY+/x4heMFAAAB771ude0UQ77CWHVXfXSrkeroOEoeXYAADXQ9zK+Xk/KuE3XXCb4uw2snKNdQyJY8zgAAAMrM8K+rBYo9M9KgLXKjaSs4Xa6sseaIVFuAAAy8zgwZnqJ3Uea8C8vPkML2rWRv4eabXHwKuzAAASRvgeMNeS3sh8DAbsWbNZrdTt7cgsD4AAAAAAAA//EADcQAAICAQIEBAMFBgcAAAAAAAEDAgQFBhEABxITCBQhMCMxQRAVUXGRFjJAQoKSIkNik6Gxs//aAAgBAQABPwD+AG2/qdh9TwWU401dXnI35MrNnvNZqhNgAdgLEesMUGLmWmRBJlHYAcY50cy8oxkW5KwBIyTj1TusiI/OUo1hMxA/E8My1ArsRRZQyypTZdjuAMEobjacP3o7SG0gRuOLYoKcsU53JJjLyzpW5rnOb4Rke+vtRiILaVzHbluYGI9Tv7O0SCD+6QQfyPoeOXnLvOc0+Y+LqBVc4WjXU3U77BlBC0RmjZYiNu4bZrArG+0R3BP5DeliMRjWzZVp1q7JLWuc0pisla9+iBMQN4x3PSOOcXKvEa/0NksVTr46tlGvVbo2mrCwLy2RaDKa9p/F6eiZG5MSfQ8Y+jmcUieOyqCjK17rjk0k79mwuTfgmW20pGT5TJjvER6did/Z+fGm8Pj2+Hnytio16dU6kyJyVdMiGWaeMW2YrAj12bGoIf1HjkrrHI5nS2X1tQ5ivwmJGU+66GZv3GOx2TpARkmYTeZGdd6mTmqQJE5Ef4hxauvHO/N6HyWTzFrLUqMM7gnWbz2ztW66TdnmJLUe0iup4imCpAdRJ6ePERVqnmNXzFaAgrO4DH5CcQP82Jkky/Mw6B7IOxHHLHT2odU+HrCs08a37Q4PO5W1STaJiiwV2nKbWZKPrAOUwiM/XolsdjxqjPYTNJXS1HXzun2QKZPxj6qegtQ0PXMFsJwkYTG/UsmMhxj7GqdeUjg9MVMi7zcWKv6oyKQpNVDie5NW0QGsAJClQHSDtvsOPFEqtS5g4XHIHSqlplK4Q/CErEoxH6L9rwh5UP0hqHHyG06edZOA/wBFtC37/wB2/wBviHyC8lzoz/TLq8lVxlD8jBUrMv8A39rwjZFtfWWpqPV8OziqNkR/CddzFSP6MH2HjVGZOpNWagy/zjfzF96yPXdQaUqP+2uPteFASHNHIT/ljp5ol/XaXt/19nMG3nMfobUNvDUmXcqjF3GUKa5RjN1iKiVrgZkDeUuOT0+VdCePRrS9kLemriFVaGRsXJ0LNBwn2CLcKvQGTgz4bZk7qIB2MSSMhRhi8rk6ELE7C6d+1WVYnERm1S5/DnMD0EzAjq29N/Z8IWKLc1q7KSPotGNoLH4S+Jan/wATj9hG/HMLwZ5nPauzxwmoqNTRep7ofnsTbod9qu8BC15BnUO2bEI7EnfpJ4hXdUL0usMsOXatrdYaQZtmp81FktgBvLp9nwu58YzC8waqCv7wT5bKpg30jKHlux+gkn14xvPrAsrwlbx9pbukbhXQyH5xJMTxofXsdZtyE0UpIp1u1GLGTBnOctydxH0AA4hz60ne0vqLM1ounXwtF9qzPo+H85eXWJfzMeAJQiPoRxWjZhWULMhKz09T5D6tmeth/uJ9nk9qJmluaem7nzRatfdVyG24mjIbLAI+vS0QlxrbT2Y0HzwtaNoZi3WwLa8bOPriCXdiDYSYIQL4SIhEwkNt9o+gHEtQ5nQ3g8z2WGSsPymZsZNFS8Yri1a7VmdVMwVxjEFSRuPTjmLhK/LnkVy80fShGEMiyOQyc9zMunUTFoiyZJM5FkoEk7k9Ptat0/l9RY7y9HOW8S6Lq70XKR7dhLUMDYThMeo9Rxk+SlrUGPSnOaozmXvLfJ33pdylhlwmUTDo7kiSF7SIEB6cY7lTqbECnSpa21CrTdVldi9Ozyj3Y4Gvv0AKb1ARBO/SONMcnKeks0Mnj71qDIKaquhltz66IO6esJWwkQB6BxUVbVEh74tP0Ih0/wAF/8QAKhEAAgIBAwIFAwUAAAAAAAAAAQIDBBEFEiEABgcTIDFxEFFhFDBBQqH/2gAIAQIBAT8A+mndw6XqdaxPFKFhhmeJ3chVyuOQftz1b1zT6V6lUkc+ZcLiEjlcrzz8549XdEtCDVL8MZljq1bJjQbN67pXZR7HOWKnAAPHXhu1Kz3LRrymR0MYt1s4UZdNytwTwQD+cj1d7aNWm7nvmRSs0VuYpIp52s5YDn5yOvCHSK1XuuAQJgIk0sje5YldnPxu9XiTGkfed/b/AD5RPyY167L720DsrVg+orOZrq/p6nlplS+QSjMSArNxtH9uqlmK7VhsR58uVFdMjBwwyPT3zBcs924m5QkiVR+cbBj/ADqbTDaZYpayzGKaGRVZQ22VTlGH2OeqETQUa8TY3JEinHtwMcenX+z59V1x7kRjWN6oicNzmQE7X+R1pXh5bp3688ssbLHfaw4C43rgbB+Nh/b/AP/EADIRAAIBAwIFAAUNAAAAAAAAAAECAwQFEQYHABITICEIFCJRYRAwMTIzQUJScYORoaL/2gAIAQMBAT8A+TVe2GsdH3S3W2toneurqCCthp4FaV+nOCVUgD63jizbdaovunL/AHqmpx6pY1gauV8q4WViuVGPw489o+njSFXfNT6S0/WXSWFrk9qpUcqhTmWOMEZ+Khscb9XG9WDaXUKW94eWs6FNcGKZfoCQKUUn4v2jweNs9QO2grA6hZI5LbSM2fBD9JQ3HpKXx32tuSuFXrSU0Ma/urIf6Tu2Hmkn2nsDOSWCVC5PuWd1HG7u1uq90dM09LaZqaOnpaxZqzqs3P8AZuFKqitkDzzcXi11Vju1bbqoKKmkqJYJgpyOeNihwf1Hbs1BCu2GnhTYMfquTg59tnLP/rPFPX1NrWoZHREeIpPzqrIUyG9oOCPBAIP3EZ41FWpcr/cqtGZknq55QzeWIdy2T27N7u6b0ZomG33O6TJUJdDIkMULPy0zKpYMWwpBOeNcb8aMvNjvlHQXKoDV9sWOnHRI6cxMgdX93MrKMrnh1COQGDD8wzg/zj5r/9k=');
        $this->assertImage($image);

        // string (invalid filename - exception expected)
        $this->expectException(\InvalidArgumentException::class);
        $image = new Image('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
    }

    public function testGetInstance(): void
    {
        $img = Image::getInstance(static::$lib);
        $this->assertImage($img);
    }

    public function testLoad(): void
    {
        $image = Image::load(self::$imageLandscape, static::$lib);
        $this->assertImageLoaded($image);
    }

    public function testLoadFromFile(): void
    {
        $image = new Image(null, static::$lib);
        $image->loadFromFile(self::$imageLandscape);
        $this->assertImageLoaded($image);
    }

    public function testFromFile(): void
    {
        $image = Image::fromFile(self::$imageLandscape, static::$lib);
        $this->assertImageLoaded($image);
    }

    public function testLoadFromString(): void
    {
        $imageData = file_get_contents(self::$imageLandscape);
        $image = new Image(null, static::$lib);
        $image->loadFromString($imageData);
        $this->assertImageLoaded($image);
    }

    public function testFromString(): void
    {
        $imageData = file_get_contents(self::$imageLandscape);
        $image = Image::fromString($imageData, static::$lib);
        $this->assertImageLoaded($image);
    }

    public function testLoadFromBase64(): void
    {
        $image = new Image(null, static::$lib);
        $image->loadFromBase64('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==');
        $this->assertImageLoaded($image);
    }

    public function testFromBase64(): void
    {
        $image = Image::fromBase64('R0lGODlhAQABAIAAAAQCBP///yH5BAEAAAEALAAAAAABAAEAAAICRAEAOw==', static::$lib);
        $this->assertImageLoaded($image);
    }


    // -------------------------------------------------------------------------
    // Image info
    // -------------------------------------------------------------------------

    public function testPing(): void
    {
        $info = $this->landscape->ping();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('path', $info);
        $this->assertEquals(self::$imageLandscape, $info['path']);
        $this->assertEquals(800, $info['width']);
        $this->assertEquals(533, $info['height']);
        $this->assertEquals('landscape', $info['orientation']);
        $this->assertEquals('jpg', $info['extension']);
        $this->assertEquals('image/jpeg', $info['mime_type']);

        // remote image with exif
        $image = new Image(self::$imageRemoteExif, static::$lib);
        $info  = $image->ping();

        $this->assertEquals(self::$imageRemoteExif, $info['path']);
        $this->assertEquals(640, $info['width']);
        $this->assertEquals(480, $info['height']);
        $this->assertEquals('landscape', $info['orientation']);
        $this->assertEquals('jpg', $info['extension']);
        $this->assertEquals('image/jpeg', $info['mime_type']);
    }

    public function testPingImage(): void
    {
        $info = Image::pingImage(self::$imageLandscape, static::$lib);

        $this->assertIsArray($info);
        $this->assertArrayHasKey('path', $info);
        $this->assertEquals(self::$imageLandscape, $info['path']);
        $this->assertEquals(800, $info['width']);
        $this->assertEquals(533, $info['height']);
        $this->assertEquals('landscape', $info['orientation']);
        $this->assertEquals('jpg', $info['extension']);
        $this->assertEquals('image/jpeg', $info['mime_type']);
    }

    public function testGetInfo(): void
    {
        $info = $this->landscape->getInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('path', $info);
        $this->assertEquals(self::$imageLandscape, $info['path']);
        $this->assertEquals(800, $info['width']);
        $this->assertEquals(533, $info['height']);
        $this->assertEquals('landscape', $info['orientation']);
        $this->assertEquals('jpg', $info['extension']);
        $this->assertEquals('image/jpeg', $info['mime_type']);

        $info = $this->portrait->getInfo();

        $this->assertEquals(533, $info['width']);
        $this->assertEquals(800, $info['height']);
        $this->assertEquals('portrait', $info['orientation']);

        // exif data included only when explicitly requested
        $image = new Image(self::$imageExifGps, static::$lib);
        $this->assertArrayNotHasKey('exif', $image->getInfo());
        $this->assertArrayHasKey('exif', $image->getInfo(true));
    }

    public function testDestroy(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->destroy();
        $info = $image->getInfo();

        $this->assertIsArray($info);
        $this->assertEmpty($info);
        $this->assertNull($image->getResource());
        $this->assertNull($image->getPath());
        $this->assertNull($image->getMimeType());
        $this->assertNull($image->getWidth());
        $this->assertNull($image->getHeight());
        $this->assertNull($image->getExtension());
        $this->assertNull($image->getOrientation());
    }


    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function testGetResource(): void
    {
        $this->assertResource($this->landscape->getResource());

        $empty = new Image(null, static::$lib);
        $this->assertNull($empty->getResource());
    }

    public function testGetPath(): void
    {
        $this->assertEquals(self::$imageLandscape, $this->landscape->getPath());
    }

    public function testGetExtensionFromPath(): void
    {
        $this->assertEquals('jpg', $this->landscape->getExtensionFromPath());
    }

    public function testGetDimensions(): void
    {
        $this->assertIsArray($this->landscape->getDimensions());
        $this->assertEquals([800, 533], $this->landscape->getDimensions());

        $this->expectException(\Exception::class);
        $image = new Image('invalid', static::$lib);
        $image->getDimensions();
    }

    public function testGetWidth(): void
    {
        $this->assertEquals(800, $this->landscape->getWidth());

        $this->expectException(\Exception::class);
        $image = new Image('invalid', static::$lib);
        $image->getWidth();
    }

    public function testGetHeight(): void
    {
        $this->assertEquals(533, $this->landscape->getHeight());

        $this->expectException(\Exception::class);
        $image = new Image('invalid', static::$lib);
        $image->getHeight();
    }

    public function testGetMimeType(): void
    {
        $this->assertEquals('image/jpeg', $this->landscape->getMimeType());

        $png = new Image(static::$imageTransparentPng, static::$lib);
        $this->assertEquals('image/png', $png->getMimeType());

        $gif = new Image(static::$imageTransparentGif, static::$lib);
        $this->assertEquals('image/gif', $gif->getMimeType());
    }

    public function testGetExtension(): void
    {
        $this->assertEquals('jpg', $this->landscape->getExtension());
        $this->assertEquals('jpg', $this->landscape->getExtension(false));
        $this->assertEquals('.jpg', $this->landscape->getExtension(true));
    }

    public function testGetOrientation(): void
    {
        $this->assertEquals(Image::ORIENTATION_LANDSCAPE, $this->landscape->getOrientation());
        $this->assertEquals(Image::ORIENTATION_PORTRAIT, $this->portrait->getOrientation());
    }


    // -------------------------------------------------------------------------
    // Persistence
    // -------------------------------------------------------------------------

    public function testSave(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);

        // LOAD JPG -> SAVE JPG (valid path)
        $path   = $this->getTmpPath('test_save_1.jpg');
        $result = $image->save($path);

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $info = Image::pingImage($path, static::$lib);
        $this->assertEquals('image/jpeg', $info['mime_type']);

        // LOAD JPG -> SAVE JPG (invalid path)
        $result = $image->save('/invalid_path/image.jpg');
        $this->assertFalse($result);

        // LOAD JPG -> SAVE PNG (valid path)
        $path   = $this->getTmpPath('test_save_2.png');
        $result = $image->save($path, 80, 'png');

        $this->assertTrue($result);
        $this->assertFileExists($path);

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        $this->assertEquals('image/png', $mimeType);
    }

    public function testRevert(): void
    {
        $img = new Image(static::$imageLandscape, static::$lib); // 800x533

        $img->resizeToBestFit(700, 700);
        $this->assertEquals([700, 466], $img->getDimensions());

        $img->revert();

        $this->assertResource($img->getResource());
        $this->assertEquals([800, 533], $img->getDimensions());
    }

    public function testToString(): void
    {
        $data = $this->landscape->toString(100, 'jpg');

        $this->assertIsString($data);
        $this->assertNotEmpty($data);

        $image = Image::fromString($data);
        $info  = $image->getInfo();

        $this->assertImage($image);
        $this->assertEquals('image/jpeg', $info['mime_type']);
    }

    public function testToBase64(): void
    {
        $data = $this->landscape->toBase64(100, 'png');

        $this->assertIsString($data);
        $this->assertNotEmpty($data);

        $image = Image::fromBase64($data);
        $info  = $image->getInfo();

        $this->assertImage($image);
        $this->assertEquals('image/png', $info['mime_type']);
    }

    public function testToDataUri(): void
    {
        $data = $this->landscape->toDataUri(100, 'png');

        $this->assertMatchesRegularExpression(
            '/^data:image\/png;base64,[A-Za-z0-9+\/]+=*$/',
            $data
        );
    }


    // -------------------------------------------------------------------------
    // Resize operations
    // -------------------------------------------------------------------------

    public function testResizeWithEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resize(100, 600, true);
        $info = $image->getInfo();

        $this->assertEquals([100, 600], $image->getDimensions());
        $this->assertEquals(100, $info['width']);
        $this->assertEquals(600, $info['height']);
    }

    public function testResizeWithoutEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resize(100, 600, false);
        $info = $image->getInfo();

        $this->assertNotEquals([100, 600], $image->getDimensions());
        $this->assertNotEquals(100, $info['width']);
        $this->assertNotEquals(600, $info['height']);
    }

    public function testResizeTransparentBackground(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);

        $image->setBackgroundColor(Image::COLOR_TRANSPARENT);
        $image->thumbnail(100, 100);
        $path = $this->getTmpPath('resize_transparent_100.png');
        $image->save($path, 100, 'png');
        $this->assertFileExists($path);

        $image->revert();
        $image->setBackgroundColor([0, 0, 255, 0.50]);
        $image->thumbnail(100, 100);
        $path = $this->getTmpPath('resize_transparent_50.png');
        $image->save($path, 100, 'png');
        $this->assertFileExists($path);

        $image->revert();
        $image->setBackgroundColor([0, 0, 255, 0.25]);
        $image->thumbnail(100, 100);
        $path = $this->getTmpPath('resize_transparent_25.png');
        $image->save($path, 100, 'png');
        $this->assertFileExists($path);

        $image->destroy();
    }

    public function testResizeToWidthReduce(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToWidth(300, true);
        $info = $image->getInfo();

        $this->assertEquals([300, 200], $image->getDimensions());
        $this->assertEquals(300, $info['width']);
        $this->assertEquals(200, $info['height']);
    }

    public function testResizeToWidthNoEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToWidth(1000, false);

        $this->assertEquals([800, 533], $image->getDimensions());
    }

    public function testResizeToHeightEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToHeight(600, true);
        $info = $image->getInfo();

        $this->assertEquals([901, 600], $image->getDimensions());
        $this->assertEquals(901, $info['width']);
        $this->assertEquals(600, $info['height']);
    }

    public function testResizeToHeightNoEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToHeight(700, false);
        $info = $image->getInfo();

        $this->assertEquals([800, 533], $image->getDimensions());
        $this->assertEquals(800, $info['width']);
        $this->assertEquals(533, $info['height']);
    }

    public function testResizeToShortSideEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToShortSide(600, true);
        $info = $image->getInfo();

        $this->assertEquals([901, 600], $image->getDimensions());
        $this->assertEquals(901, $info['width']);
        $this->assertEquals(600, $info['height']);
    }

    public function testResizeToShortSideNoEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToShortSide(700, false);
        $info = $image->getInfo();

        $this->assertEquals([800, 533], $image->getDimensions());
        $this->assertEquals(800, $info['width']);
        $this->assertEquals(533, $info['height']);
    }

    public function testResizeToLongSideEnlarge(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToLongSide(600, true);
        $info = $image->getInfo();

        $this->assertEquals([600, 400], $image->getDimensions());
        $this->assertEquals(600, $info['width']);
        $this->assertEquals(400, $info['height']);
    }

    public function testResizeToLongSideReduce(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->resizeToLongSide(700, false);
        $info = $image->getInfo();

        $this->assertEquals([700, 466], $image->getDimensions());
        $this->assertEquals(700, $info['width']);
        $this->assertEquals(466, $info['height']);
    }

    public function testResizeToBestFit(): void
    {
        $landscape = new Image(static::$imageLandscape, static::$lib); // 800x533
        $portrait  = new Image(static::$imagePortrait, static::$lib); // 533x800

        // reduce both
        $landscape->resizeToBestFit(700, 700);
        $this->assertEquals([700, 466], $landscape->getDimensions());

        $portrait->resizeToBestFit(700, 700);
        $this->assertEquals([466, 700], $portrait->getDimensions());

        $landscape->revert();
        $portrait->revert();

        // enlarge blocked (allowEnlarge: false)
        $landscape->resizeToBestFit(900, 900, false);
        $this->assertEquals([800, 533], $landscape->getDimensions());

        $portrait->resizeToBestFit(900, 900, false);
        $this->assertEquals([533, 800], $portrait->getDimensions());

        $landscape->revert();
        $portrait->revert();

        // enlarge allowed (allowEnlarge: true)
        $landscape->resizeToBestFit(900, 900, true);
        $this->assertEquals([900, 600], $landscape->getDimensions());

        $portrait->resizeToBestFit(900, 900, true);
        $this->assertEquals([600, 900], $portrait->getDimensions());
    }


    // -------------------------------------------------------------------------
    // Crop operations
    // -------------------------------------------------------------------------

    public function testCrop(): void
    {
        $image = new Image(static::$imageLandscape, static::$lib); // 800x533

        // allowEnlarge: false, crop within bounds
        $image->crop(0, 0, 200, 150, false);
        $this->assertEquals([200, 150], $image->getDimensions());

        // allowEnlarge: false, crop wider than image -> clamped to image width
        $image->revert();
        $image->crop(0, 0, 800, 800, false);
        $this->assertEquals([800, 533], $image->getDimensions());

        // allowEnlarge: true, canvas expanded
        $image->revert();
        $image->crop(0, 0, 850, 850, true);
        $this->assertEquals([850, 850], $image->getDimensions());

        // allowEnlarge: false, origin offset so remainder is smaller than crop
        $image->revert();
        $image->crop(400, 400, 900, 900, false);
        $this->assertEquals([400, 133], $image->getDimensions());
    }

    /**
     * @dataProvider cropAutoProvider
     */
    public function testCropAuto(int $mode, string $label): void
    {
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->cropAuto(400, 400, $mode);

        $path = $this->getTmpPath("cropAuto_400x400_{$label}.jpg");
        $image->save($path);

        $this->assertEquals([400, 400], $image->getDimensions());
    }

    public static function cropAutoProvider(): array
    {
        return [
            'center'       => [Image::CROP_CENTER,       'center'],
            'left'         => [Image::CROP_LEFT,         'left'],
            'right'        => [Image::CROP_RIGHT,        'right'],
            'top'          => [Image::CROP_TOP,          'top'],
            'bottom'       => [Image::CROP_BOTTOM,       'bottom'],
            'top_left'     => [Image::CROP_TOP_LEFT,     'top_left'],
            'top_right'    => [Image::CROP_TOP_RIGHT,    'top_right'],
            'bottom_left'  => [Image::CROP_BOTTOM_LEFT,  'bottom_left'],
            'bottom_right' => [Image::CROP_BOTTOM_RIGHT, 'bottom_right'],
        ];
    }


    // -------------------------------------------------------------------------
    // Thumbnail
    // -------------------------------------------------------------------------

    /**
     * @dataProvider thumbnailProvider
     */
    public function testThumbnail(int $w, int $h, bool $fill, bool $allowEnlarge, array $expected, string $label): void
    {
        $image = new Image(static::$imageLandscape, static::$lib); // 800x533
        $image->thumbnail($w, $h, $fill, $allowEnlarge);

        $path = $this->getTmpPath("thumbnail_{$label}.jpg");
        $image->save($path);

        $this->assertEquals($expected, $image->getDimensions());
    }

    public static function thumbnailProvider(): array
    {
        return [
            'landscape no-fill'      => [300, 150, false, false, [300, 150], '300x150'],
            'portrait-ratio no-fill' => [150, 300, false, false, [150, 300], '150x300'],
            'landscape fill'         => [300, 150, true,  false, [300, 150], 'fill_300x150'],
            'portrait-ratio fill'    => [150, 300, true,  false, [150, 300], 'fill_150x300'],
            'enlarge wide no-fill'   => [900, 300, false, true,  [900, 300], '900x300'],
            'enlarge tall no-fill'   => [300, 800, false, true,  [300, 800], '300x800'],
            'enlarge tall fill'      => [300, 800, true,  true,  [300, 800], 'fill_300x800'],
            'square no-fill'         => [500, 500, false, false, [500, 500], '500x500'],
            'square fill'            => [500, 500, true,  false, [500, 500], 'fill_500x500'],
        ];
    }

    public function testThumbnailPortraitSource(): void
    {
        $img = new Image(static::$imagePortrait, static::$lib); // 533x800
        $img->thumbnail(300, 150, false, false);

        $path = $this->getTmpPath('thumbnail_portrait_300x150.jpg');
        $img->save($path);

        $this->assertEquals([300, 150], $img->getDimensions());
    }


    // -------------------------------------------------------------------------
    // Transform
    // -------------------------------------------------------------------------

    public function testFlip(): void
    {
        $image = new Image(static::$imageTransparentPng, static::$lib); // 610x621

        $image->flip(Image::FLIP_VERTICAL);
        $this->assertEquals([610, 621], $image->getDimensions());

        $image->revert();
        $image->flip(Image::FLIP_HORIZONTAL);
        $this->assertEquals([610, 621], $image->getDimensions());

        $image->revert();
        $image->flip(Image::FLIP_BOTH);
        $this->assertEquals([610, 621], $image->getDimensions());
    }

    public function testRotate(): void
    {
        $jpg = new Image(static::$imageLandscape, static::$lib);
        $jpg->rotate(45, '#ff0000');
        $this->assertEquals([941, 942], $jpg->getDimensions());
        $path = $this->getTmpPath('rotate_45.jpg');
        $jpg->save($path, 100);
        $this->assertFileExists($path);
        $jpg->destroy();

        $png = new Image(static::$imageTransparentPng, static::$lib);
        $png->rotate(45);
        $this->assertEquals([869, 870], $png->getDimensions());
        $path = $this->getTmpPath('rotate_45.png');
        $png->save($path, 100);
        $this->assertFileExists($path);
        $png->destroy();

        $gif = new Image(static::$imageTransparentGif, static::$lib);
        $gif->rotate(45, '#ff0000');
        $this->assertEquals([869, 870], $gif->getDimensions());
        $path = $this->getTmpPath('rotate_45.gif');
        $gif->save($path, 100);
        $this->assertFileExists($path);
        $gif->destroy();
    }

    public function testSetBackgroundColor(): void
    {
        // jpeg: background color set, dimensions unchanged
        $image = new Image(static::$imageLandscape, static::$lib);
        $image->setBackgroundColor('ff0000');
        $this->assertEquals([800, 533], $image->getDimensions());
        $path = $this->getTmpPath('setBackgroundColor_ff0000.jpg');
        $image->save($path);
        $this->assertFileExists($path);
        $image->destroy();

        // transparent png: background applied during resize
        $image = new Image(static::$imageTransparentPng, static::$lib);
        $image->setBackgroundColor([255, 0, 0, 1.0]);
        $image->thumbnail(200, 100);
        $this->assertEquals([200, 100], $image->getDimensions());
        $path = $this->getTmpPath('setBackgroundColor_ff0000_transparent.png');
        $image->save($path, 100, 'png');
        $this->assertFileExists($path);
        $image->destroy();

        // transparent gif: background applied during resize
        $image = new Image(static::$imageTransparentGif, static::$lib);
        $image->setBackgroundColor([255, 0, 0, 0.0]);
        $image->thumbnail(200, 100);
        $this->assertEquals([200, 100], $image->getDimensions());
        $path = $this->getTmpPath('setBackgroundColor_ff0000_transparent.gif');
        $image->save($path, 100, 'gif');
        $this->assertFileExists($path);
        $image->destroy();
    }


    // -------------------------------------------------------------------------
    // Color utilities
    // -------------------------------------------------------------------------

    /**
     * @dataProvider normalizeColorProvider
     */
    public function testNormalizeColor($input, array $expected): void
    {
        $this->assertEquals($expected, Image::normalizeColor($input));
    }

    public static function normalizeColorProvider(): array
    {
        $rgba = static function (int $r, int $g, int $b, float $a): array {
            return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a];
        };
        return [
            '3-char hex'      => ['000', $rgba(0, 0, 0, 1.0)],
            '3-char hex hash' => ['#ccc', $rgba(204, 204, 204, 1.0)],
            '6-char hex'      => ['00ccFF', $rgba(0, 204, 255, 1.0)],
            '8-char hex hash' => ['#00ccFF7f', $rgba(0, 204, 255, 0.5)],
            'named color'     => ['black', $rgba(0, 0, 0, 1.0)],
            'rgb array'       => [[0, 0, 255], $rgba(0, 0, 255, 1.0)],
            'rgba array'      => [[0, 0, 255, 0.5], $rgba(0, 0, 255, 0.5)],
        ];
    }

    /**
     * @dataProvider hex2percentageProvider
     */
    public function testHex2percentage(string $hex, int $expected): void
    {
        $this->assertEquals($expected, Image::hex2percentage($hex));
    }

    public static function hex2percentageProvider(): array
    {
        return [
            '00 -> 0%'   => ['00', 0],
            '1A -> 10%'  => ['1A', 10],
            '73 -> 45%'  => ['73', 45],
            '80 -> 50%'  => ['80', 50],
            'D9 -> 85%'  => ['D9', 85],
            'FF -> 100%' => ['FF', 100],
        ];
    }

    /**
     * @dataProvider percentage2hexProvider
     */
    public function testPercentage2hex(int $pct, string $expected): void
    {
        $this->assertEquals($expected, Image::percentage2hex($pct));
    }

    public static function percentage2hexProvider(): array
    {
        return [
            '0% -> 00'   => [0,   '00'],
            '10% -> 1a'  => [10,  '1a'],
            '45% -> 73'  => [45,  '73'],
            '50% -> 80'  => [50,  '80'],
            '85% -> d9'  => [85,  'd9'],
            '100% -> ff' => [100, 'ff'],
        ];
    }

    /**
     * @dataProvider hex2rgbaProvider
     */
    public function testHex2rgba($input, array $expected): void
    {
        $this->assertEquals($expected, Image::hex2rgba($input));
    }

    public static function hex2rgbaProvider(): array
    {
        $rgba = static function (int $r, int $g, int $b, float $a): array {
            return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a];
        };
        return [
            '#RRGGBB'   => ['#0000ff',   $rgba(0, 0, 255, 1.0)],
            'RRGGBB'    => ['0000ff',    $rgba(0, 0, 255, 1.0)],
            '#RGB'      => ['#00f',      $rgba(0, 0, 255, 1.0)],
            'RGB'       => ['00f',       $rgba(0, 0, 255, 1.0)],
            '#RRGGBBAA' => ['#0000FFCC', $rgba(0, 0, 255, 0.8)],
            'RRGGBBAA'  => ['0000FF00',  $rgba(0, 0, 255, 0.0)],
            'rgb array' => [[0, 0, 255], $rgba(0, 0, 255, 0.0)],
        ];
    }

    /**
     * @dataProvider rgba2hexProvider
     */
    public function testRgba2hex(array $input, string $expected): void
    {
        $this->assertEquals($expected, Image::rgba2hex($input));
    }

    public static function rgba2hexProvider(): array
    {
        return [
            'black'             => [[0, 0, 0],         '#000000'],
            'green'             => [[0, 255, 0],        '#00ff00'],
            'mixed'             => [[0, 127, 255],      '#007fff'],
            'fully transparent' => [[0, 127, 255, 0.0], '#007fff00'],
            'half transparent'  => [[0, 127, 255, 0.5], '#007fff80'],
            'fully opaque'      => [[0, 127, 255, 100], '#007fffff'],
        ];
    }


    // -------------------------------------------------------------------------
    // EXIF
    // -------------------------------------------------------------------------

    public function testReadExifData(): void
    {
        $image = Image::load(static::$imageExif, static::$lib);

        // extended info: false
        $info = $image->getInfo();
        $this->assertArrayNotHasKey('exif', $info);

        // extended info: true
        $info = $image->getInfo(true);
        $image->destroy();
        $this->assertArrayHasKey('exif', $info);

        // get exif without loading an image
        $image = new Image(null, static::$lib);
        $exif  = $image->readExifData(static::$imageExif);

        $this->assertIsArray($exif);
        $this->assertArrayHasKey('FileName', $exif);
    }

    public function testGetExifProperty(): void
    {
        /*
        Image Exif:
        [ExposureTime] => 1/345
        [FNumber] => 80/10
        [ExposureProgram] => 2
        [ISOSpeedRatings] => 125
         */

        // image without exif data
        $image = Image::load(static::$imageNoMetadata, static::$lib);
        $value = $image->getExifData('ExposureTime');
        $image->destroy();
        $this->assertNull($value);

        // image with exif data
        $image = Image::load(static::$imageExif, static::$lib);

        // direct property
        $value = $image->getExifData('ExposureTime');
        $this->assertEquals('1/345', $value);

        // multiple properties
        $value = $image->getExifData(['DateTimeOriginal', 'ExposureTime']);
        $this->assertEquals([
            'DateTimeOriginal' => '2000:11:07 10:41:43',
            'ExposureTime'     => '1/345',
        ], $value);

        // using named properties
        $this->assertEquals('2000:11:07 10:41:43', $image->getExifData('date_created'));
        $this->assertEquals(125, $image->getExifData('iso'));
        $this->assertEquals('1/345', $image->getExifData('exposure'));
    }

    public function testGetDateCreated(): void
    {
        $image = Image::load(static::$imageExif, static::$lib);

        $dt = $image->getDateCreated();
        $this->assertInstanceOf(DateTimeImmutable::class, $dt);
        $this->assertEquals('2000-11-07T10:41:43+00:00', $dt->format(DATE_ATOM));
    }

    public function testGetGps(): void
    {
        $image = Image::load(static::$imageExifGps, static::$lib);

        // decimal
        $gps = $image->getGps();

        $this->assertIsArray($gps);
        $this->assertEquals([
            'lat' => round(43.468365, 6),
            'lng' => round(11.881635, 6),
        ], $gps);

        // degrees minutes seconds (DMS)
        $gps = $image->getGps(true);
        $this->assertIsArray($gps);
        $this->assertEquals([
            'lat' => ['degrees' => 43, 'minutes' => 28, 'seconds' => 6.114],
            'lng' => ['degrees' => 11, 'minutes' => 52, 'seconds' => 53.8859999],
        ], $gps);
        $image->destroy();

        // gps not available
        $gps = $this->landscape->getGps();
        $this->assertNull($gps);
    }


    // -------------------------------------------------------------------------
    // Misc
    // -------------------------------------------------------------------------

    public function testGetExtensionFromPathExplicit(): void
    {
        $im = Image::getInstance(static::$lib);

        $this->assertEquals('png', $im->getExtensionFromPath('C:\\test\\sample_image.png'));
        $this->assertEquals('jpg', $im->getExtensionFromPath('C:\\test\\sample_image.new.jpg'));
        $this->assertEquals('jpg', $im->getExtensionFromPath('sample_image.jpg'));
        $this->assertEquals('jpg', $im->getExtensionFromPath('/images/sample_image.jpg'));
    }

}
