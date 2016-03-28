<?php

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