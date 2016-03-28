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

class Stitch extends CommandAbstract
{
    /**
     * Execute stitch
     * @return bool
     */
    public function run()
    {
        $result = $this->parse();
        if (is_null($result) || $this->hasErrors()) {
            return false;
        }

        try {
            $path = dirname($result->map);
            $mapFile = json_decode(file_get_contents($result->map), true);
            foreach($mapFile as &$row) {
                foreach($row as &$file) {
                    $file = realpath($path . DIRECTORY_SEPARATOR . $file);
                }
            }

            $imageSlicer = new \Mosaic\Image\Stitch();
            $imageSlicer->initialize(
                $mapFile,
                $result->spacex,
                $result->spacey,
                $result->output,
                $result->bgcolor,
                $result->bordercolor,
                $result->borderwidth,
                $result->fitstrategy
            );

            $imageSlicer->stitch();
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
        return "Stitches a collection of images into a single mosaic image\n" . parent::getOptionsList();
    }

    /**
     * Performs option initialization
     * @param OptionCollection $specs
     * @throws \GetOptionKit\Exception
     */
    protected function initialize(OptionCollection $specs)
    {
        $specs->add('m|map:', 'Map file')->isa('File');
        $specs->add('o|output:', 'Output file')->isa('String');
        $specs->add('x|spacex:', 'Gap width')->isa('Number');
        $specs->add('y|spacey:', 'Gap height')->isa('Number');
        $specs->add('b|bgcolor?', 'Background color')->isa('String');
        $specs->add('c|bordercolor?', 'Border color')->isa('String');
        $specs->add('w|borderwidth?', 'Border width')->isa('Number');
        $specs->add('s|fitstrategy?', 'Fit Strategy (default 1)')->isa('Number');
    }
}