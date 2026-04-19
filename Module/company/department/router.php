<?php

namespace Company\Department;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Department\\Controller\\DepartmentCtrl";

// Toggle active/inactive (đặt TRƯỚC route /:id để không bị match nhầm)
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/phongban/:id/active', 'POST,PUT', $ctrl, 'updateStatusActive', new RouterFilter("/rest/phongban"))
);

// Tạo mới / cập nhật phòng ban
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/phongban(/:id)', 'POST,PUT', $ctrl, 'updateDepartment', new RouterFilter("/rest/phongban"))
);

// Lấy chi tiết 1 phòng ban
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/phongban/:id', 'GET', $ctrl, 'getDepartment', new RouterFilter("/rest/phongban"))
);

// Xóa phòng ban
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/phongban/:id', 'DELETE', $ctrl, 'deleteDepartment', new RouterFilter("/rest/phongban"))
);

// Lấy danh sách phòng ban
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/phongban', 'GET', $ctrl, 'getDepartments', new RouterFilter("/rest/phongban"))
);
