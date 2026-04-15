<?php

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

require_once BASE_DIR . '/Module/company/mvc/autoload.php';

//khoi tao ung dung
$application = new \Company\MVC\Bootstrap();