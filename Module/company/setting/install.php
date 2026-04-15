<?php

namespace Company\Setting;

use Company\MVC\Module;
use Company\User\Model\PrivilegeMapper;

$module = new Module("company/setting");

$module->initDatabase();
$privMapper = PrivilegeMapper::makeInstance();


