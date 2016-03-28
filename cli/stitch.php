<?php

define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
require_once APPLICATION_PATH . '/vendor/autoload.php';

$stitch = new \Mosaic\Cli\Command\Stitch();

if (count($argv) > 1) {
    if ($stitch->run()) {
        printf("Stitch executed successfully!\n");
        die();
    } else {
        foreach ($stitch->getErrors() as $error) {
            printf("Error: %s \n", $error);
        }
        echo $stitch->getOptionsList();
    }
} else {
    echo $stitch->getOptionsList();
}
die(1);