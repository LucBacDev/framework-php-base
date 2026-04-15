<?php

namespace CompanyUI\User;

use Company\MVC\Layout;
use Company\MVC\Module;

class UserCtrl extends \Company\MVC\Controller {

    protected $layout;

    /**
     * @var Module 
     */
    protected $module;
    protected $auth;
    protected $session;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
        $this->module = Module::getInstance('companyui/user');
        $this->auth = \Company\Auth\Auth::getInstance();
        $this->session = \Company\Session\Session::getInstance();
    }

    function login() {

        if (\Company\Auth\Auth::getInstance()->getUser()) {
            header('location:' . url('/' . $_SESSION["arrSiteID"][0] . '/study/list'));
            die;
        }

        if ($this->session->get('arrSiteID')) {
            $this->session->set('arrSiteID', NULL);
        }

        $this->layout->renderReact('LoginPage');
    }

    function userList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('UserList');
    }

    function roleList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('RoleList');
    }

    function mergeSite($siteID) {
        $this->layout
            ->setSiteID($siteID)
            ->renderReact('MergeSite');
    }

}
