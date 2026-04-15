<?php

namespace Company\VersionInfo\Controller;

use Company\Auth\Auth;
use Company\VersionInfo\Lib\Parsedown;
use Company\VersionInfo\Model as M;

class VersionInfoCtrl extends \Company\MVC\Controller {

    /** @var M\UserMapper */
    protected $auth;

    function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function getVersionInfo($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireLogin();
        $changelog = file_get_contents(BASE_DIR . '/CHANGELOG.txt');
//        $html_content = str_replace("<", "<", $changelog);
//        $html_content = str_replace(">", ">", $html_content);
//        $versions = str_replace("\r\n", "<br/>", $html_content);
//        $result = array(
//            'rows' => $versions,
//        );
        $this->outputJSON(result(true, (new Parsedown())->text($changelog)));
    }

}
