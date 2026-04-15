<?php
foreach(scandir(__DIR__) as $item) {
    //not load this file, only load js file
    if(strpos($item, '.js') === false || strpos($item, 'autoload') !== false) {
        continue;
    }
    echo ";\n//$item\n";
    readfile(__DIR__ . '/' . $item);
    
}