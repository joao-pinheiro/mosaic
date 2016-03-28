<?php
/**
 * Mosaic
 *
 * Copyright (c) 2016, Joao Pinheiro
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Mosaic
 * @package    Cli
 * @copyright  Copyright (c) 2016 Joao Pinheiro
 * @version    0.5
 */

namespace Mosaic\Image;

use Imagine\Image\Box;
use Imagine\Image\Palette\Color\RGB;
use Imagine\Image\Point;
use Imagine\Imagick\Imagine;
use Mosaic\Image\Exception\StitchException;

class Stitch
{
    const COLOR_REGEX = '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/';
    const STRATEGY_CROP = 1;
    const STRATEGY_RESIZE = 2;

    /**
     * @var array
     */
    protected $stitchMap = [];

    /**
     * @var int
     */
    protected $horizontalBlocks = 0;

    /**
     * @var int
     */
    protected $verticalBlocks = 0;

    /**
     * @var int
     */
    protected $horizontalGap = 0;

    /**
     * @var int
     */
    protected $verticalGap = 0;

    /**
     * @var int
     */
    protected $cellWidth = 0;

    /**
     * @var int
     */
    protected $cellHeight = 0;

    /**
     * @var string
     */
    protected $backgroundColor = '#FFFFFF';

    /**
     * @var string
     */
    protected $borderColor = '#FFFFFF';

    /**
     * @var int
     */
    protected $borderWidth = 0;

    /**
     * @var string
     */
    protected $outputFile = '';

    /**
     * @var int
     */
    protected $strategy = self::STRATEGY_RESIZE;

    /**
     * @var array
     */
    protected $validStrategies = [
        self::STRATEGY_RESIZE,
        self::STRATEGY_CROP
    ];

    /**
     * Initialize stitcher
     * @param array $stitchMap
     * @param int $xGap
     * @param int $yGap
     * @param string $outputFile
     * @param string|null $backgroundColor
     * @param string|null $borderColor
     * @param int $borderWidth
     * @param int $fitStrategy
     */
    public function initialize(
        array $stitchMap, $xGap, $yGap, $outputFile, $backgroundColor = null, $borderColor = null,
        $borderWidth = null, $fitStrategy = null
    )
    {
        $this->setStitchMap($stitchMap)
            ->setHorizontalGap($xGap)
            ->setVerticalGap($yGap)
            ->setOutputFile($outputFile);

        if (!is_null($backgroundColor)) {
            $this->setBackgroundColor($backgroundColor);
        }
        if (!is_null($borderColor)) {
            $this->setBorderColor($borderColor);
        }
        if (!is_null($borderWidth)) {
            $this->setBorderWidth($borderWidth);
        }
        if (!is_null($fitStrategy)) {
            $this->setStrategy($fitStrategy);
        }

    }

    /**
     * Perform stitching
     * @throws StitchException
     */
    public function stitch()
    {
        $hBlocks = $this->getHorizontalBlocks();
        $vBlocks = $this->getVerticalBlocks();

        $cellWidth = $this->getCellWidth();
        $cellHeight = $this->getCellHeight();
        $borderWidth = $this->getBorderWidth();
        $totalCellWidth = $cellWidth + ($borderWidth * 2);
        $totalCellHeight = $cellHeight + ($borderWidth * 2);

        $hGap = $this->getHorizontalGap();
        $vGap = $this->getVerticalGap();

        $totalWidth = $hBlocks > 1
            ? (($hBlocks - 1) * ($totalCellWidth + $hGap)) + $totalCellWidth
            : $totalCellWidth;
        $totalHeight = $vBlocks > 1
            ? (($vBlocks - 1) * ($totalCellHeight + $vGap)) + $totalCellHeight
            : $totalCellHeight;

        $size = new Box($totalWidth, $totalHeight);
        $palette = new \Imagine\Image\Palette\RGB();
        $bgColor = $palette->color($this->getBackgroundColor());
        $borderColor = $palette->color($this->getBorderColor());
        $image = (new Imagine())->create($size, $bgColor);

        $x = 0;
        $y = 0;
        for($yy = 0; $yy < $vBlocks; $yy++) {
            for ($xx = 0; $xx < $hBlocks; $xx++) {
                $block = $this->getBlock($xx, $yy, $cellWidth, $cellHeight, $bgColor);

                // process border if necessary
                if ($borderWidth > 0) {
                    $size = new Box($totalCellWidth, $totalCellHeight);
                    $newBlock = (new Imagine())->create($size, $borderColor);
                    $newBlock->paste($block, new Point($borderWidth, $borderWidth));
                    $block = $newBlock;
                }

                $image->paste($block, new Point($x, $y));
                $x = $x + $totalCellWidth + $hGap;
            }
            $x = 0;
            $y = $y + $totalCellHeight + $vGap;

        }
        $image->save($this->getOutputFile());
    }

    /**
     * Initialize stitch map
     * @param int $stitchFileMap
     * @return $this
     * @throws StitchException
     */
    protected function setStitchMap(array $stitchFileMap)
    {
        $result = [];
        $rows = count($stitchFileMap);
        $cellWidth = 0;
        $cellHeight = 0;
        if (empty($rows)) {
            throw new StitchException('Mosaic must have at least 1 row');
        }
        $cols = null;
        $y = 0;
        $imagine = new Imagine();
        foreach ($stitchFileMap as $row) {
            $x = 0;
            if (empty($row)) {
                throw new StitchException('Mosaic cannot have an empty row');
            }
            if ($cols == null) {
                $cols = count($row);
            } else {
                if (count($row) !== $cols) {
                    throw new StitchException(sprintf('Invalid column count at row %s', $y + 1));
                }
            }
            foreach ($row as $item) {
                if (!file_exists($item)) {
                    throw new StitchException(sprintf('File %s not found', $item));
                }
                $image = $imagine->open($item);
                if ($image->getSize()->getWidth() > $cellWidth) {
                    $cellWidth = $image->getSize()->getWidth();
                }
                if ($image->getSize()->getHeight() > $cellHeight) {
                    $cellHeight = $image->getSize()->getHeight();
                }
                $result[$y][$x] = $image;
                $x++;
            }
            $y++;
        }
        $this->horizontalBlocks = $cols;
        $this->verticalBlocks = count($result);
        $this->cellWidth = $cellWidth;
        $this->cellHeight = $cellHeight;
        $this->stitchMap = $result;

        return $this;
    }

    /**
     * Retrieve stitch map
     * @return array
     */
    protected function getStitchMap()
    {
        return $this->stitchMap;
    }

    /**
     * Retrieve vertical block count
     * @return int
     */
    protected function getVerticalBlocks()
    {
        return $this->verticalBlocks;
    }

    /**
     * Retrieve horizontal block count
     * @return int
     */
    protected function getHorizontalBlocks()
    {
        return $this->horizontalBlocks;
    }

    /**
     * Set horizontal gap
     * @param int $pixels
     * @return $this
     * @throws StitchException
     */
    protected function setHorizontalGap($pixels)
    {
        $pixels = (int)$pixels;
        if ($pixels < 0) {
            throw new StitchException('Invalid horizontal gap');
        }
        $this->horizontalGap = $pixels;
        return $this;
    }

    /**
     * Set vertical gap
     * @param int $pixels
     * @return $this
     * @throws StitchException
     */
    protected function setVerticalGap($pixels)
    {
        $pixels = (int)$pixels;
        if ($pixels < 0) {
            throw new StitchException('Invalid vertical gap');
        }
        $this->verticalGap = $pixels;
        return $this;
    }

    /**
     * Retrieve vertical gap
     * @return int
     */
    protected function getVerticalGap()
    {
        return $this->verticalGap;
    }

    /**
     * Retrieve horizontal gap
     * @return int
     */
    protected function getHorizontalGap()
    {
        return $this->horizontalGap;
    }

    /**
     * Define output file
     * @param string $fileName
     * @return $this
     * @throws StitchException
     */
    protected function setOutputFile($fileName)
    {
        if (empty($fileName)) {
            throw new StitchException(sprintf('Invalid output file %s', $fileName));
        }
        $this->outputFile = $fileName;
        return $this;
    }

    /**
     * Retrieve output file
     * @return string
     */
    protected function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * Define background color
     * @param string $color
     * @return $this
     * @throws StitchException
     */
    protected function setBackgroundColor($color)
    {
        if (!$this->isValidColor($color)) {
            throw new StitchException(sprintf('Invalid background color %s', $color));
        }
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Retrieve background color
     * @return string
     */
    protected function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Define border color
     * @param string $color
     * @return $this
     * @throws StitchException
     */
    protected function setBorderColor($color)
    {
        if (!$this->isValidColor($color)) {
            throw new StitchException(sprintf('Invalid border color %s', $color));
        }
        $this->borderColor = $color;
        return $this;
    }

    /**
     * Retrieve border color
     * @return string
     */
    protected function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * Define placement strategy
     * @param int $strategy
     * @throws StitchException
     */
    protected function setStrategy($strategy)
    {
        if (!in_array($strategy, $this->validStrategies)) {
            throw new StitchException(sprintf('Invalid fitting strategy %s', $strategy));
        }
        $this->strategy = $strategy;
    }

    /**
     * Retrieve placement strategy
     * @return int
     */
    protected function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Define the border width
     * @param int $width
     * @return $this
     * @throws StitchException
     */
    protected function setBorderWidth($width)
    {
        $width = (int) $width;
        if ($width < 0) {
            throw new StitchException('Invalid border width');
        }
        $this->borderWidth = $width;
        return $this;
    }

    /**
     * Retrieve border width
     * @return int
     */
    protected function getBorderWidth()
    {
        return $this->borderWidth;
    }

    /**
     * Define cell width
     * @param int $pixels
     * @return $this
     * @throws StitchException
     */
    protected function setCellWidth($pixels)
    {
        $pixels = (int)$pixels;
        if ($pixels < 1) {
            throw new StitchException('Invalid cell width');
        }
        $this->cellWidth = $pixels;
        return $this;
    }

    /**
     * Retrieve cell width
     * @return int
     */
    protected function getCellWidth()
    {
        return $this->cellWidth;
    }

    /**
     * Define cell height
     * @param int $pixels
     * @return $this
     * @throws StitchException
     */
    protected function setCellHeight($pixels)
    {
        $pixels = (int)$pixels;
        if ($pixels < 1) {
            throw new StitchException('Invalid cell height');
        }
        $this->cellHeight = $pixels;
        return $this;
    }

    /**
     * Retrieve cell width
     * @return int
     */
    protected function getCellHeight()
    {
        return $this->cellHeight;
    }

    /**
     * Retrieve a block from the map
     * @param int $width
     * @param int $height
     * @param int $pixelWidth
     * @param int $pixelHeight
     * @param RGB $bgColor
     * @return \Imagine\Imagick\Image
     * @throws StitchException
     */
    protected function getBlock($width, $height, $pixelWidth, $pixelHeight, RGB $bgColor)
    {
        if (!isset($this->stitchMap[$height][$width])) {
            throw new StitchException('Invalid map coordinates %s %s', $width, $height);
        }

        /** @var \Imagine\Imagick\Image $image */
        $image = $this->stitchMap[$height][$width];

        $sx = $pixelWidth - $image->getSize()->getWidth();
        $sy = $pixelHeight - $image->getSize()->getHeight();
        if ($sx < 0 || $sy < 0) {
            switch($this->getStrategy()) {
                case self::STRATEGY_RESIZE:
                    $image->resize(new Box($pixelWidth, $pixelHeight));
                    break;

                case self::STRATEGY_CROP:
                    $image->crop(new Point(0, 0), new Box($pixelWidth, $pixelHeight));
                    break;
            }
        }

        $sx = $pixelWidth - $image->getSize()->getWidth();
        $sy = $pixelHeight - $image->getSize()->getHeight();
        if ($sx > 0 || $sy > 0) {
            $newImage = (new Imagine())->create(new Box($pixelWidth, $pixelHeight), $bgColor);
            $newImage->paste($image, new Point($sx >> 1, $sy >> 1));
            $image = $newImage;
        }

        return $image;
    }

    /**
     * Validates a color string
     * @param string $value
     * @return bool
     */
    protected function isValidColor($value)
    {
        return preg_match(self::COLOR_REGEX, $value) === 1;
    }
}
