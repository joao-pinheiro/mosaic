<?php

define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
require_once APPLICATION_PATH . '/vendor/autoload.php';

$slice = new \Mosaic\Cli\Command\Slice();

if (count($argv) > 1) {
    if ($slice->run()) {
        printf("Slice executed successfully!\n");
        die();
    } else {
        foreach ($slice->getErrors() as $error) {
            printf("Error: %s \n", $error);
        }
        echo $slice->getOptionsList();
    }
} else {
    echo $slice->getOptionsList();
}
die(1);