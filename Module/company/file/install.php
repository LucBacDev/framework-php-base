<?php

use Company\MVC\Module;
use Company\SQL\DB;
use Company\User\Model\PrivilegeMapper;

$module = new Module("company/file");

$module->initDatabase();
