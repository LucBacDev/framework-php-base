<?php

namespace Company\User;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;
use Company\User\Controller\PrivilegeCtrl;

/**
 * User router
 */
$userCtrl = "\\Company\\User\\Controller\\UserCtrl";
R::getInstance()->addRoute(new MVC('/:siteID/rest/users(/:id)', 'POST,PUT', $userCtrl, "updateUser", new RouterFilter("/rest/users")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/users/changePassword', 'POST', $userCtrl, "changePassword", new RouterFilter("/rest/users")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:id', 'GET', $userCtrl, "getUser", new RouterFilter("/rest/users")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/users', 'GET', $userCtrl, "getUsers", new RouterFilter("/rest/users")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:id', 'DELETE', $userCtrl, "deleteUser", new RouterFilter("/rest/users")));

/**
 * Department route
 */
$depCtrl = "\\Company\\User\\Controller\\DepartmentCtrl";
R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id/active', 'POST,PUT', $depCtrl, "updateStatusActive", new RouterFilter("/rest/departments")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/departments(/:id)', 'POST,PUT', $depCtrl, "updateDepartment", new RouterFilter("/rest/departments")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id', 'GET', $depCtrl, "getDep", new RouterFilter("/rest/departments")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id', 'DELETE', $depCtrl, "deleteDep", new RouterFilter("/rest/departments")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/departments', 'GET', $depCtrl, "getDeps", new RouterFilter("/rest/departments")));

/**
 * Role router
 */
$roleCtrl = "\\Company\\User\\Controller\\RoleCtrl";
R::getInstance()->addRoute(new MVC('/:siteID/rest/roles(/:id)', 'POST,PUT', $roleCtrl, "updateRole", new RouterFilter("/rest/roles")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/roles', 'GET', $roleCtrl, "getRoles", new RouterFilter("/rest/roles")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id', 'GET', $roleCtrl, "getRole", new RouterFilter("/rest/roles")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id', 'DELETE', $roleCtrl, "deleteRole", new RouterFilter("/rest/roles")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id/users', 'GET', $roleCtrl, "getRoleUser", new RouterFilter("/rest/roles")));

R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/setUserRoleDefault/:id', 'POST,PUT', $roleCtrl, "setUserRoleDefault", new RouterFilter("/rest/roles")));

$controller = PrivilegeCtrl::class;
R::getInstance()->addRoute(new MVC('/rest/privileges/all', 'GET', $controller, "getAllPrivs", new RouterFilter("privilege")));

$userCtrl = "\\Company\\User\\Controller\\UserCtrl";
// lấy danh sách các site user được phân quyền
R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:userID/sites', 'GET', $userCtrl, "getUserSites", new RouterFilter("user")));
// ghép tài khoản lại với nhau
R::getInstance()->addRoute(new MVC('/:siteID/rest/user/merge', 'POST,PUT', $userCtrl, "updateMergeSite", new RouterFilter("user")));


$roleCtrl = "\\Company\\User\\Controller\\RoleCtrl";

R::getInstance()->addRoute(new MVC('/:siteID/test1', 'GET', "\\Company\\User\\Controller\\UserCtrl", "test"));

R::getInstance()->addRoute(new MVC('/:siteID/rest/listCustomDisplay', 'GET', $roleCtrl, "getListCustomDisplay"));
R::getInstance()->addRoute(new MVC('/:siteID/rest/listDiagConfig', 'GET', $roleCtrl, "getListDiagConfig"));
