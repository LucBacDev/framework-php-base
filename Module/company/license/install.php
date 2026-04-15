<?php

use Company\MVC\Module;
use Company\SQL\DB;

$module = new Module("company/license");

$module->initDatabase();
$db = DB::getInstance();

//$privMapper = PrivilegeMapper::makeInstance();
//
//$privMapper->startTrans();
//$privGroup = ['id' => 'admin', 'name' => 'Quản trị'];
//$privMapper->createGroupIfNotExists($privGroup['id'], $privGroup['name']);
//
//$privMapper->completeTransOrFail();
