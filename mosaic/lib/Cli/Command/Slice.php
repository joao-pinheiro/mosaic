<?php

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