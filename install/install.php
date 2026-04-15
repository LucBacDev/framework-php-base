<?php

use Company\MVC\Module;
use Company\SQL\DB;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/Docroot/index.php';
DB::getInstance()->debug = 1;

$modules = Module::getModules();
\Company\Auth\Auth::registerAuthMethod(new \Company\Auth\LocalDBAuth);

//install composer
chdir(BASE_DIR);

//include các file construct vào hệ thống để load nhanh
$install = [
    'description' => 'This file for autoloading modules files at runtime',
    'constructs' => []
];

foreach (Module::getSortArray() as $moduleName) {
    $module = $modules[$moduleName];
    $constructPath = $module->getConstructPath();
    if ($constructPath) {
        $install['constructs'][] = str_replace([BASE_DIR, "\\"], ["", "/"], $constructPath);
    }
}

file_put_contents(BASE_DIR . '/install.lock', json_encode($install, JSON_PRETTY_PRINT));

//chạy các file install.php trong các module

foreach (Module::getSortArray() as $moduleName) {
    $module = $modules[$moduleName];
    echo "\nInstalling module: " . $module->getName();
    $module->executeInstallFile();
    $module->checkExistsOrCreateModuleRecord();
}

echo "\nINSTALL DONE!\n";