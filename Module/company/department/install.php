<?php

use Company\MVC\Module;

$module = new Module("company/department");
$module->initDatabase();
$module->checkExistsOrCreateModuleRecord();
