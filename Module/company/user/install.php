<?php

use Company\MVC\Module;
use Company\Setting\Model\FieldMapper;
use Company\Setting\Model\FormMapper;
use Company\SQL\DB;
use Company\User\Model\CustomListMapper;
use Company\User\Model\PrivilegeMapper;
use Company\User\Model\RoleMapper;
use Company\User\Model\UserMapper;

$module = new Module("company/user");

$module->initDatabase();

$db = DB::getInstance();
$privMapper = PrivilegeMapper::makeInstance();
$cusListMapper = CustomListMapper::makeInstance();

$privMapper->startTrans();
$privGroup = ['id' => 'admin', 'name' => 'Quản trị hệ thống'];
$privMapper->createGroupIfNotExists($privGroup['id'], $privGroup['name']);

require __DIR__ . '/sql/privileges.data.php'; //lấy danh sách privilege

//insert priv
foreach ($exports as $priv) {
    $privMapper->createPrivilegeIfNotExists($priv['id'], $privGroup['id'], $priv['name'], arrData($priv, 'desc'));
}

// tự động tạo tài khoản admin
$admin = UserMapper::makeInstance()
    ->filterLogin('localdb', 'admin')
    ->getEntity();
if (!$admin->id) {
    $admin->id = UserMapper::makeInstance()->updateUser(
        NULL,
        [
            'fullname' => "Quản trị hệ thống",
            'depFK' => 0,
            'active' => 1,
            'jobTitle' => 'Chức vụ',
            'login' => [
                'localdb' => [
                    'account' => 'admin',
                    'password' => 'Admin@123456'
                ]
            ],
            'siteFK' => 'master',
            'noDelete' => 1
        ]
    );
}

// tự động tạo role admin
if (!RoleMapper::makeInstance()->filterID('admin')->isExists()) {
    RoleMapper::makeInstance()->updateRole('admin', [
        'id' => 'admin',
        'name' => 'Quản trị hệ thống',
        'users' => [$admin->id['data']['id']],
        'siteFK' => 'master',
        'privileges' => ['fullcontrol']
    ]);
}

$privMapper->completeTransOrFail();

$formMapper = FormMapper::makeInstance();

$formMapper->startTrans();

$formData = [
    //    ["id" => "WebConfig", "name" => "Cấu hình trang web"],
//    ["id" => "RemoteConfig", "name" => "Truy cập từ xa"],
//    ["id" => "PrintedFormConfig", "name" => "Cấu hình quản trị mẫu in"],
    ["id" => "SecurityConfig", "name" => "Cấu hình bảo mật"]
];

$dataToUpdate = [];
foreach ($formData as $data) {
    if (!$formMapper->filterID($data["id"])->isExists())
        $dataToUpdate[] = $data;
}

if (!empty($dataToUpdate))
    $formMapper->insert($dataToUpdate);

$fieldData = [
    [
        'id' => "PasswordLength",
        'formID' => "SecurityConfig",
        'label' => 'Độ dài mật khẩu',
        'dataType' => 'text',
        'defaultVal' => '6',
        'isGlobal' => 1,
        '`desc`' => ''
    ],
    [
        'id' => "ComplexPassword",
        'formID' => "SecurityConfig",
        'label' => 'Yêu cầu mật khẩu phức tạp',
        'dataType' => 'text',
        'defaultVal' => '0',
        'isGlobal' => 1,
        '`desc`' => 'Bao gồm chữ hoa, chữ thuờng, số, ký tự đặc biệt (VD: Abc@1234). Điền có(1) hoặc không(0)'
    ]
];
$fieldMapper = FieldMapper::makeInstance();
$dataToUpdate = [];
foreach ($fieldData as $data) {
    if (!$fieldMapper->filterID($data["id"])->isExists())
        $dataToUpdate[] = $data;
}

if (!empty($dataToUpdate))
    $fieldMapper->insert($dataToUpdate);

$formMapper->completeTransOrFail();