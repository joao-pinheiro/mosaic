<?php

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