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

class Map extends CommandAbstract
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
            $map = [];
            $x = 0;
            $y = 0;

            foreach (glob($result->mask) as $file) {
                if ($y == $result->height) {
                    break;
                }
                $map[$y][$x] = basename($file);
                if ($x == $result->width - 1) {
                    $y++;
                    $x = 0;
                } else {
                    $x++;
                }
            }

            file_put_contents($result->output, json_encode($map));

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
        return "Generates a file map from a list of files\n" . parent::getOptionsList();
    }

    /**
     * Performs option initialization
     * @param OptionCollection $specs
     * @throws \GetOptionKit\Exception
     */
    protected function initialize(OptionCollection $specs)
    {
        $specs->add('f|mask:', 'File list mask')->isa('String');
        $specs->add('w|width:', 'Horizontal blocks')->isa('Number');
        $specs->add('h|height:', 'Vertical blocks')->isa('Number');
        $specs->add('o|output:', 'Output map file')->isa('String');
    }
}