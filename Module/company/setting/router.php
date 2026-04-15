<?php

namespace Company\Setting;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * Form route
 */
$formCtrl = "\\Company\\Setting\\Controller\\FormCtrl";
$fieldCtrl = "\\Company\\Setting\\Controller\\FieldCtrl";
//R::getInstance()->addRoute(new MVC('/rest/setting/forms/:id', 'POST,PUT', $formCtrl, "updateForm"));
//R::getInstance()->addRoute(new MVC('/rest/setting/forms/:id', 'GET', $formCtrl, "getForm"));
R::getInstance()->addRoute(new MVC('/rest/setting/forms', 'GET', $formCtrl, "getForms", new RouterFilter("setting")));

R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/updateIntegrate', 'POST,PUT', $formCtrl, "updateSettingIntegrate", new RouterFilter("setting")));

/**
 * Field route
 */
R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/forms/fields', 'POST,PUT', $fieldCtrl, "updateValueFields", new RouterFilter("setting")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/forms/:id/fields', 'GET', $fieldCtrl, "getFieldsFormId", new RouterFilter("setting")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/getData', 'GET', $fieldCtrl, "getDataSetting", new RouterFilter("setting")));
