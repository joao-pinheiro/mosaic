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

namespace Mosaic\Cli\Command;

use GetOptionKit\OptionCollection;

class Slice extends CommandAbstract
{
    const DEFAULT_MAP = 'map.json';

    /**
     * Execute slice
     * @return bool
     */
    public function run()
    {
        $result = $this->parse();
        if (is_null($result) || $this->hasErrors()) {
            return false;
        }

        try {
            $imageSlicer = new \Mosaic\Image\Slice();
            $imageSlicer->initialize($result->file, $result->width, $result->height, $result->output, $result->mask);
            $map = $imageSlicer->slice();

            $this->generateMapFile($map, $result->output);

            return true;

        } catch (\Exception $e) {
            $this->addError($e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve options list
     * @return string
     */
    public function getOptionsList()
    {
        return "Slices an image into a mosaic of witdth x height tiles \n" . parent::getOptionsList();
    }

    /**
     * Performs option initialization
     * @param OptionCollection $specs
     */
    protected function initialize(OptionCollection $specs)
    {
        $specs->add('f|file:', 'Image file (png/jpg)')->isa('File');
        $specs->add('w|width:', 'Horizontal blocks')->isa('Number');
        $specs->add('h|height:', 'Vertical blocks')->isa('Number');
        $specs->add('m|mask?', 'Output mask(default is filename-x-y.extension)')->isa('String');
        $specs->add('o|output?', 'Output directory)')->isa('String');
    }

    /**
     * Write map file
     * @param array $map
     * @param string|null $outputDir
     */
    protected function generateMapFile($map, $outputDir = null)
    {
        $outputDir = is_null($outputDir) ? '' : realpath($outputDir);
        file_put_contents(implode(DIRECTORY_SEPARATOR, [$outputDir, self::DEFAULT_MAP]), json_encode($map));
    }
}