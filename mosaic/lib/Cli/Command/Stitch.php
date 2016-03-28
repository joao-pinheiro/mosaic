<?php

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