<?php

namespace Company\Employee;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Employee\\Controller\\EmployeeCtrl";

// Lấy nhân sự theo phòng ban (đặt TRƯỚC route /:id để không bị match nhầm)
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/nhansu/phongban/:depID', 'GET', $ctrl, 'getEmployeesByDep', new RouterFilter("/rest/nhansu"))
);

// Tạo mới / cập nhật nhân sự
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/nhansu(/:id)', 'POST,PUT', $ctrl, 'updateEmployee', new RouterFilter("/rest/nhansu"))
);

// Lấy chi tiết 1 nhân sự
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/nhansu/:id', 'GET', $ctrl, 'getEmployee', new RouterFilter("/rest/nhansu"))
);

// Xóa nhân sự
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/nhansu/:id', 'DELETE', $ctrl, 'deleteEmployee', new RouterFilter("/rest/nhansu"))
);

// Lấy danh sách nhân sự (có phân trang)
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/nhansu', 'GET', $ctrl, 'getEmployees', new RouterFilter("/rest/nhansu"))
);
