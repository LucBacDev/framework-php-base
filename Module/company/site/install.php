<?php

use Company\MVC\Module;
use Company\Site\Model\SiteMapper;
use Company\SQL\DB;
use Company\User\Model\PrivilegeMapper;

$module = new Module("company/site");

$module->initDatabase();
$db = DB::getInstance();

$privMapper = PrivilegeMapper::makeInstance();

$privMapper->startTrans();

// tự động thêm master site
SiteMapper::makeInstance()->updateSite('master', [
    'name' => "master site",
    'shortName' => "ms site",
    'active' => 1,
    'id' => 'master'
    ]
);

$privMapper->completeTransOrFail();
