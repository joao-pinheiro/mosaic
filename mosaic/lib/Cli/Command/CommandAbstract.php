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
use GetOptionKit\OptionParser;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;
use Mosaic\Mixin\ErrorStack;

abstract class CommandAbstract
{
    use ErrorStack;

    /**
     * @var \GetOptionKit\OptionCollection
     */
    protected $optionCollection = null;

    /**
     * @var \GetOptionKit\OptionParser
     */
    protected $optionParser = null;

    /**
     * @var \GetOptionKit\OptionPrinter\ConsoleOptionPrinter
     */
    protected $optionPrinter = null;

    /**
     * Retrieve options collection
     * @return OptionCollection
     */
    public function getOptionCollection()
    {
        if (null == $this->optionCollection) {
            $this->optionCollection = new OptionCollection();
            $this->initialize($this->optionCollection);
        }
        return $this->optionCollection;
    }

    /**
     * Retrieve option Parser
     * @return OptionParser
     */
    public function getOptionParser()
    {
        if (null == $this->optionParser) {
            $this->optionParser = new OptionParser($this->getOptionCollection());
        }
        return $this->optionParser;
    }

    /**
     * Retrieve option printer
     * @return ConsoleOptionPrinter
     */
    public function getOptionPrinter()
    {
        if (null == $this->optionPrinter) {
            $this->optionPrinter = new ConsoleOptionPrinter();
        }
        return $this->optionPrinter;
    }

    /**
     * Parses $argv or the parameter array
     * @param array|null $contents
     * @return null|\GetOptionKit\Option[]|\GetOptionKit\OptionResult
     */
    public function parse($contents = null)
    {
        global $argv;

        $this->clearErrors();
        if (is_null($contents)) {
            $contents = $argv;
        }
        try {
            return $this->getOptionParser()->parse($contents);

        } catch (\Exception $e) {
            $this->addError($e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve options list
     * @return string
     */
    public function getOptionsList()
    {
        return $this->getOptionPrinter()->render($this->getOptionCollection());
    }

    /**
     * Initialize options
     * @param OptionCollection $specs
     * @return void
     */
    abstract protected function initialize(OptionCollection $specs);
}