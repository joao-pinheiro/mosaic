<?php

define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
require_once APPLICATION_PATH . '/vendor/autoload.php';

$map = new \Mosaic\Cli\Command\Map();

if (count($argv) > 1) {
    if ($map->run()) {
        printf("Map executed successfully!\n");
        die();
    } else {
        foreach ($map->getErrors() as $error) {
            printf("Error: %s \n", $error);
        }
        echo $map->getOptionsList();
    }
} else {
    echo $map->getOptionsList();
}
die(1);