<?php

namespace CompanyUI\Home;

use Company\MVC\Layout;
use Company\MVC\Module;

class HomeCtrl extends \Company\MVC\Controller
{

    protected $layout;

    /**
     * @var Module 
     */
    protected $module;
    protected $auth;

    function init()
    {
        parent::init();
        $this->layout = Layout::getLayout('admin');
        $this->module = Module::getInstance('companyui/home');
        $this->auth = \Company\Auth\Auth::getInstance();
    }

    function home()
    {
        $this->auth->requireLogin();
        $user = $this->auth->getUser();
        $arrSiteID = \Company\User\Model\UserMapper::makeInstance()->getSitesID($user->id);
        //        if (count($arrSiteID) > 1) {
//            header('location:' . url('/' . $arrSiteID[0] . '/users/sites'));
//        } else {
//            header('location:' . url('/' . $arrSiteID[0] . '/users'));
//        }
        header('location:' . url('/' . $arrSiteID[0] . '/users/sites'));
        die;
    }

}
