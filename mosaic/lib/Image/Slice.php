<?php

namespace Mosaic\Image;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Imagick\Imagine;
use Mosaic\Image\Exception\SliceException;

class Slice
{
    const PLACEHOLDER_NAME = '{name}';
    const PLACEHOLDER_WIDTH = '{width}';
    const PLACEHOLDER_HEIGHT = '{height}';
    const PLACEHOLDER_EXTENSION = '{extension}';

    /**
     * @var \Imagine\Imagick\Image;
     */
    protected $image = null;

    /**
     * @var string
     */
    protected $imageName = '';

    /**
     * @var string
     */
    protected $outputDirectory = '';

    /**
     * @var int
     */
    protected $horizontalBlocks = 0;

    /**
     * @var int
     */
    protected $verticalBlocks = 0;

    /**
     * @var string
     */
    protected $mask = '{name}-{width}-{height}.{extension}';

    /**
     * Initialize Slicer
     * @param string $fileName
     * @param int $width
     * @param int $height
     * @param string|null $outputDir
     * @param string|null $mask
     * @throws SliceException
     */
    public function initialize($fileName, $width, $height, $outputDir = null, $mask = null)
    {
        $this->setImage($fileName)
            ->setHorizontalBlocks($width)
            ->setVerticalBlocks($height);
        if (!is_null($outputDir)) {
            $this->setOutputDirectory($outputDir);
        }
        if (!is_null($mask)) {
            $this->setMask($mask);
        }
    }

    /**
     * Performs image slicing
     * @throws SliceException
     */
    public function slice()
    {
        $sourceImage = $this->getImage();
        if (empty($sourceImage)) {
            throw new SliceException('Source image not initialized');
        }

        $mapResult = [];
        $w = $sourceImage->getSize()->getWidth();
        $h = $sourceImage->getSize()->getHeight();

        $vblocks = $this->getVerticalBlocks();
        $hblocks = $this->getHorizontalBlocks();

        $blockWidth = (int)($w / $hblocks);
        $blockHeight = (int)($h / $vblocks);

        $x = 0;
        $y = 0;
        for ($yy = 0; $yy < $vblocks; $yy++) {
            for ($xx = 0; $xx < $hblocks; $xx++) {
                $outputName = $this->assembleOutputName($xx + 1, $yy + 1);
                $sourceImage->copy()
                    ->crop(new Point($x, $y), new Box($blockWidth, $blockHeight))
                    ->save($outputName);
                $x = $x + $blockWidth;
                $mapResult[$yy][$xx] = basename($outputName);
            }
            $x = 0;
            $y = $y + $blockHeight;
        }
        return $mapResult;
    }

    /**
     * Set source image
     * @param string $fileName
     * @return $this
     * @throws SliceException
     */
    protected function setImage($fileName)
    {
        if (file_exists($fileName)) {
            $imagine = new Imagine();
            $this->image = $imagine->open($fileName);
            $this->imageName = $fileName;
            return $this;
        }
        throw new SliceException(sprintf('File %s not found', $fileName));
    }

    /**
     * Set block height
     * @param int $count
     * @return $this
     * @throws SliceException
     */
    protected function setVerticalBlocks($count)
    {
        $count = (int)$count;
        if ($count < 1 || $count > $this->getImage()->getSize()->getHeight()) {
            throw new SliceException('Invalid height value');
        }
        $this->verticalBlocks = $count;
        return $this;
    }

    /**
     * Set block width
     * @param int $count
     * @return $this
     * @throws SliceException
     */
    protected function setHorizontalBlocks($count)
    {
        $count = (int)$count;
        if ($count < 1 || $count > $this->getImage()->getSize()->getWidth()) {
            throw new SliceException('Invalid width value');
        }
        $this->horizontalBlocks = $count;
        return $this;
    }

    /**
     * Retrieve desired horizontal block count
     * @return int
     */
    protected function getHorizontalBlocks()
    {
        return $this->horizontalBlocks;
    }

    /**
     * Retrieve desired vertical block count
     * @return int
     */
    protected function getVerticalBlocks()
    {
        return $this->verticalBlocks;
    }

    /**
     * set output mask
     * @param string $mask
     * @return $this
     * @throws SliceException
     */
    protected function setMask($mask)
    {
        if (empty($mask)) {
            throw new SliceException(sprintf('Invalid mask %s', $mask));
        }
        return $this;
    }

    /**
     * Retrieve output mask
     * @return string
     */
    protected function getMask()
    {
        return $this->mask;
    }

    /**
     * Retrieve source image
     * @return \Imagine\Imagick\Image
     */
    protected function getImage()
    {
        return $this->image;
    }

    /**
     * Define the output directory
     * @param string $dir
     * @return $this
     * @throws SliceException
     */
    protected function setOutputDirectory($dir)
    {
        $dir = realpath($dir);
        if (!is_dir($dir)) {
            throw new SliceException(sprintf('Invalid output directory %s', $dir));
        }
        $this->outputDirectory = $dir;
        return $this;
    }

    /**
     * Retrieve output directory
     * @return string
     */
    protected function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * Generate output name for a given block
     * @param int $hblock
     * @param int $vblock
     * @return string
     */
    protected function assembleOutputName($hblock, $vblock)
    {
        $tmp = explode('.', $this->imageName);
        $ext = array_pop($tmp);

        $fileName = str_replace(
            [
                self::PLACEHOLDER_NAME,
                self::PLACEHOLDER_WIDTH,
                self::PLACEHOLDER_HEIGHT,
                self::PLACEHOLDER_EXTENSION
            ],
            [
                implode('.', $tmp),
                $hblock,
                $vblock,
                $ext
            ],
            $this->getMask()
        );

        $outputDir = $this->getOutputDirectory();
        return empty($outputDir) ? $fileName : implode(DIRECTORY_SEPARATOR, [$outputDir, $fileName]);
    }
}
