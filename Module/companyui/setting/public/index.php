<?php

outputFile(__DIR__, 'construct.js');
outputDir(__DIR__ . '/model');
outputDir(__DIR__ . '/view');

function outputDir($dir, $firstFiles = [], $lastFiles = []){
    $exclude = array_merge($firstFiles, $lastFiles);
    foreach($firstFiles as $item){
        outputFile($dir, $item);
    }
    foreach (scandir($dir) as $item) {
        if (strpos($item, '.js') !== false && !in_array($item, $exclude)) {
            outputFile($dir, $item);
        }
    }
    foreach($lastFiles as $item){
        outputFile($dir, $item);
    }
}

function outputFile($dir, $item) {
    echo "\n\n//$item\n";
    readfile($dir . '/' . $item);
}
