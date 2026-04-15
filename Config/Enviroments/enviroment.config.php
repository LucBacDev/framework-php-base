<?php

$config = "development.config.php";

foreach (scandir(__DIR__) as $item) {
    if ($item == 'enviroment.config.php' || $item == 'development.config.php' || $item == 'development.config.php.example' || strpos($item, '.config.php') === false) {
        continue;
    }

    $config = $item;
    break;
}

//quét thư mục để lấy file config
$include = BASE_DIR . '/Config/Includes';
foreach (scandir($include) as $item) {
    if (strpos($item, '.config.php') === false) {
        continue;
    }

    require_once $include . '/' . $item;
}

$exports['enviroment'] = str_replace('.config.php', '', $config);
