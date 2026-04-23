<?php

use Company\MVC\Module;

$module = new Module("company/employee");
$module->initDatabase();
$module->checkExistsOrCreateModuleRecord();
