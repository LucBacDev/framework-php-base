<?php

spl_autoload_register(function ($class) {
    $parts = explode("\\", $class);
    //tên class tối thiểu 3 thành phần mới đủ
    if (count($parts) < 3) {
        return;
    }
    //Do cách đặt tên của composer không viết hoa, phải convert 2 thư mục đầu tiên thành chữ thường
    $parts[0] = strtolower($parts[0]);
    $parts[1] = strtolower($parts[1]);
    $file = BASE_DIR . '/Module/' . implode('/', $parts) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

//load thư mục global, các function, class sẽ không thuộc namespace nào
$dir = __DIR__ . '/global/';
$files = ['MacroInterface.php', 'Macro.php', 'Collection.php', 'DateTimeEx.php', 'Fn.php'];
foreach ($files as $file) {
    require $dir . $file;
}