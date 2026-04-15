<?php

use Company\MVC\Module;
use Company\SQL\DB;
use Company\User\Model\PrivilegeMapper;

$module = new Module("company/dict");

$module->initDatabase();
$db = DB::getInstance();
$privMapper = PrivilegeMapper::makeInstance();

$privMapper->startTrans();
$privGroup = ['id' => 'dict', 'name' => 'Quản trị danh mục động'];
$privMapper->createGroupIfNotExists($privGroup['id'], $privGroup['name']);

require __DIR__ . '/sql/privileges.data.php'; //lấy danh sách privilege
//insert priv
foreach ($exports as $priv) {
    $privMapper->createPrivilegeIfNotExists($priv['id'], $privGroup['id'], $priv['name'], $priv['siteFK'], arrData($priv, 'desc'));
}
$privMapper->completeTransOrFail();
