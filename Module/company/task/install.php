<?php

use Company\MVC\Module;

$module = new Module("company/task");
$module->initDatabase();
$module->checkExistsOrCreateModuleRecord();
